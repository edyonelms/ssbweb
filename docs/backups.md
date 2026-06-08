# Backups + Disaster Recovery

Nightly snapshots of the database and every uploaded file go to a
dedicated AWS S3 bucket. If the production server is wiped, the bucket
is the source of truth — every row and every document can be brought
back from there.

```
backup S3 bucket
├── db/
│   ├── db_2026-06-09_023001.sql.gz
│   ├── db_2026-06-10_023001.sql.gz
│   └── ...
├── files/
│   ├── uploads/avatars/...
│   ├── uploads/universities/...
│   ├── uploads/students/<id>/...
│   ├── uploads/announcements/...
│   └── _manifest_2026-06-09_023001.txt
└── healthcheck/
    └── last-backup-run.txt
```

---

## 1. AWS — one-time setup

You only do this **once** per environment.

### 1.1 Create the backup bucket

1. AWS Console → **S3** → **Create bucket**.
2. Name: `ssbeducation-backups` (or anything — match what you put in `AWS_BACKUP_BUCKET`).
3. Region: **ap-south-1 (Mumbai)** — same region as the app for cheap traffic.
4. **Block all public access** — keep it on, this bucket must stay private.
5. **Bucket versioning** → **Enable**. This is the safety net for "oops I deleted a file".
6. **Default encryption** → SSE-S3 (free) or SSE-KMS if you already use KMS.
7. Click **Create bucket**.

### 1.2 Add a lifecycle policy

Versioning will grow forever without one — turn this on so old versions don't bleed money.

1. Open the bucket → **Management** → **Create lifecycle rule**.
2. Apply to the entire bucket.
3. Rule:
   - **Move current versions** to **Glacier Instant Retrieval** after **30 days**.
   - **Expire non-current versions** after **90 days** (keeps three months of deleted-file history).
   - Leave current versions to never expire — DB dumps you want forever.

### 1.3 Create the IAM user

You **don't** want the app's runtime AWS keys to also have permission to delete the backup bucket. Make a separate user.

1. AWS Console → **IAM** → **Users** → **Add user**.
2. Name: `ssbeducation-backups`. Access type: **Programmatic access**.
3. Attach inline policy (replace `ssbeducation-backups` with your bucket name):

   ```json
   {
       "Version": "2012-10-17",
       "Statement": [
           {
               "Sid": "AppWriteBackups",
               "Effect": "Allow",
               "Action": [
                   "s3:ListBucket",
                   "s3:GetObject",
                   "s3:PutObject",
                   "s3:DeleteObject"
               ],
               "Resource": [
                   "arn:aws:s3:::ssbeducation-backups",
                   "arn:aws:s3:::ssbeducation-backups/*"
               ]
           }
       ]
   }
   ```

   > **Tighter:** drop `s3:DeleteObject` and rely on lifecycle policy
   > expiration. Then nothing the app does can wipe a backup.

4. **Save the access key + secret** — you can't view the secret again.

### 1.4 Drop the creds into `.env` on the production server

```env
# Existing AWS_* (your runtime / public-uploads creds) stay as-is.

AWS_BACKUP_BUCKET=ssbeducation-backups
AWS_BACKUP_REGION=ap-south-1
AWS_BACKUP_ACCESS_KEY_ID=AKIA…
AWS_BACKUP_SECRET_ACCESS_KEY=…
```

Then:

```bash
php artisan config:clear
```

---

## 2. Verify the setup

```bash
# Should print "Backup disk reachable — bucket: ssbeducation-backups"
# then proceed to dump the DB and mirror files.
php artisan backup:run

# Quick sanity check on what landed in S3:
php artisan backup:list db
php artisan backup:list files --recursive --limit=50
```

If you see `AWS_BACKUP_BUCKET is not configured` or `Access Denied`,
recheck the IAM policy + `.env` then `php artisan config:clear`.

---

## 3. Schedule the nightly job

Laravel's scheduler does the work — you just need **one** cron entry on
the server (already standard for any Laravel deploy):

```bash
crontab -e
```

Add:

```cron
* * * * * cd /var/www/ssbweb && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler ticks every minute and decides what runs when. `backup:run`
is already wired for **02:30 server time** (set `APP_TIMEZONE=Asia/Kolkata`
in `.env` so that's 02:30 IST).

Verify the schedule is registered:

```bash
php artisan schedule:list
# expect a line like:  0 30 2 * * *   php artisan backup:run …
```

---

## 4. Restore drill

Practice this **once** so you know it works before you actually need it.

### 4.1 List what's available

```bash
# Most recent DB dumps:
php artisan backup:list db

# Mirrored file objects:
php artisan backup:list files --recursive
```

### 4.2 Restore the database

```bash
# Pull the dump down to storage/app/backup-restored/<filename>:
php artisan backup:download db/db_2026-06-09_023001.sql.gz

# Inspect (sanity-check it's not empty):
gunzip -c storage/app/backup-restored/db_2026-06-09_023001.sql.gz | head -40

# Restore — this OVERWRITES the target database:
gunzip < storage/app/backup-restored/db_2026-06-09_023001.sql.gz \
  | mysql -u <db-user> -p <db-name>
```

Postgres equivalent if you're on pg:

```bash
gunzip < storage/app/backup-restored/db_*.sql.gz \
  | psql -U <user> -d <database>
```

### 4.3 Restore the uploaded files

Files mirror to `files/<original-relative-path>` in the bucket. To pull
them all back into `storage/app/public`, install the AWS CLI on the server
and run:

```bash
aws s3 sync \
  s3://ssbeducation-backups/files/ \
  storage/app/public/ \
  --exclude '_manifest_*'
```

…or restore individual files with:

```bash
php artisan backup:download files/uploads/students/42/photo.jpg \
  --out=storage/app/public/uploads/students/42/photo.jpg
```

If your uploads disk is *already* on S3 (`FILESYSTEM_PUBLIC_DRIVER=s3`),
sync straight from the backup bucket into the public bucket instead:

```bash
aws s3 sync \
  s3://ssbeducation-backups/files/ \
  s3://<your-public-bucket>/ \
  --exclude '_manifest_*'
```

---

## 5. Manual one-off backups

You can always trigger a backup outside the schedule:

```bash
# Full run (DB + files)
php artisan backup:run

# DB only — useful right before a risky migration
php artisan backup:run --db

# Files only
php artisan backup:run --files

# Keep the local gzipped dump on disk after upload (for inspection)
php artisan backup:run --db --keep-local
```

---

## 6. What you should still do periodically

- **Quarterly**: run a full restore drill into a staging DB. A backup
  you've never restored is a backup you don't have.
- **After major changes**: trigger `php artisan backup:run --db` manually
  before any irreversible operation (large delete, schema migration on
  prod, mass import).
- **Monitor**: tail `storage/logs/laravel.log` — the scheduler logs an
  error line if `backup:run` fails any night. Wire that to your alerting
  if you have it.
- **Watch S3 storage growth**: if it climbs faster than you expect,
  revisit the lifecycle policy — the default in this doc keeps DB
  dumps forever which can balloon if the DB grows.
