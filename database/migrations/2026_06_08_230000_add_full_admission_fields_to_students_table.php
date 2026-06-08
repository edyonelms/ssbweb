<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Backfills the student record with every field the admin/sub-admin
    // collects in the full admission form — academic placement (mode,
    // main vs lateral, course year, semester), parent + identity info,
    // address breakdown, document upload paths, and an academic-history
    // JSON blob that powers the X/XII/UG/PG/OTHER table at the bottom of
    // the form. All columns are nullable so existing rows keep working.
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Academic placement
            if (! Schema::hasColumn('students', 'mode')) {
                $table->string('mode', 20)->nullable()->after('course_id');                  // online / offline
            }
            if (! Schema::hasColumn('students', 'enrollment_type')) {
                $table->string('enrollment_type', 20)->nullable()->after('mode');            // main / lateral
            }
            if (! Schema::hasColumn('students', 'course_year')) {
                $table->unsignedTinyInteger('course_year')->nullable()->after('enrollment_type');
            }
            if (! Schema::hasColumn('students', 'semester')) {
                $table->unsignedTinyInteger('semester')->nullable()->after('course_year');
            }

            // Personal
            if (! Schema::hasColumn('students', 'father_name')) {
                $table->string('father_name')->nullable()->after('semester');
            }
            if (! Schema::hasColumn('students', 'mother_name')) {
                $table->string('mother_name')->nullable()->after('father_name');
            }
            if (! Schema::hasColumn('students', 'dob')) {
                $table->date('dob')->nullable()->after('mother_name');
            }
            if (! Schema::hasColumn('students', 'category')) {
                $table->string('category', 20)->nullable()->after('dob');                    // general/obc/sc/st/minor/nri/other
            }
            if (! Schema::hasColumn('students', 'nationality')) {
                $table->string('nationality', 64)->nullable()->after('category');
            }
            if (! Schema::hasColumn('students', 'religion')) {
                $table->string('religion', 64)->nullable()->after('nationality');
            }
            if (! Schema::hasColumn('students', 'aadhar_number')) {
                $table->string('aadhar_number', 20)->nullable()->after('religion');
            }

            // Address breakdown
            if (! Schema::hasColumn('students', 'country')) {
                $table->string('country', 64)->nullable()->after('address');
            }
            if (! Schema::hasColumn('students', 'state')) {
                $table->string('state', 64)->nullable()->after('country');
            }
            if (! Schema::hasColumn('students', 'city')) {
                $table->string('city', 64)->nullable()->after('state');
            }
            if (! Schema::hasColumn('students', 'pincode')) {
                $table->string('pincode', 12)->nullable()->after('city');
            }

            // Documents
            foreach ([
                'photo_path',
                'aadhar_front_path',
                'aadhar_back_path',
                'marksheet_x_path',
                'marksheet_xii_path',
                'marksheet_graduation_path',
                'student_sign_path',
                'abc_id_path',
                'deb_id_path',
                'other_doc_path',
            ] as $col) {
                if (! Schema::hasColumn('students', $col)) {
                    $table->string($col)->nullable();
                }
            }

            // Free-form academic history (the X/XII/UG/PG/OTHER bottom table)
            if (! Schema::hasColumn('students', 'academic_records')) {
                $table->json('academic_records')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $cols = [
                'mode', 'enrollment_type', 'course_year', 'semester',
                'father_name', 'mother_name', 'dob', 'category', 'nationality',
                'religion', 'aadhar_number', 'country', 'state', 'city', 'pincode',
                'photo_path', 'aadhar_front_path', 'aadhar_back_path',
                'marksheet_x_path', 'marksheet_xii_path', 'marksheet_graduation_path',
                'student_sign_path', 'abc_id_path', 'deb_id_path', 'other_doc_path',
                'academic_records',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('students', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
