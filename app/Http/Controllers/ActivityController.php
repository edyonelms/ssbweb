<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActivityController extends Controller
{
    /**
     * Mark the topbar Recent Activity feed as read for the current user
     * by stamping their last_seen_activity_id at the latest activity id.
     *
     * The caller may pass `last_id` (the highest id that was rendered in
     * the panel they just opened); when omitted we fall back to the live
     * MAX(id) so a stale tab also clears its own bell.
     */
    public function markSeen(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['ok' => false], 401);
        }

        if (! Schema::hasTable('activity_logs') || ! Schema::hasColumn('users', 'last_seen_activity_id')) {
            return response()->json(['ok' => true, 'last_seen_activity_id' => null]);
        }

        $latestId = (int) ($request->integer('last_id') ?: ActivityLog::max('id'));

        // Only move the watermark forward — never roll it backwards.
        if ($latestId > 0 && (int) ($user->last_seen_activity_id ?? 0) < $latestId) {
            $user->forceFill(['last_seen_activity_id' => $latestId])->save();
        }

        return response()->json([
            'ok'                    => true,
            'last_seen_activity_id' => (int) $user->fresh()->last_seen_activity_id,
        ]);
    }

    /**
     * Dismiss one or more activity log entries from the current user's
     * topbar feed. Uses a per-user soft-hide pivot — the underlying log
     * row is preserved so other users keep their copy of the entry.
     *
     * Accepts JSON or form-encoded body with `ids` as an array of
     * integers; returns the count of newly hidden rows.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['ok' => false], 401);
        }

        if (! Schema::hasTable('activity_log_hides')) {
            return response()->json(['ok' => false, 'reason' => 'pivot-missing'], 503);
        }

        $ids = collect((array) $request->input('ids', []))
            ->map(fn ($i) => (int) $i)
            ->filter(fn ($i) => $i > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['ok' => true, 'hidden' => 0]);
        }

        $now  = now();
        $rows = $ids->map(fn ($id) => [
            'user_id'         => $user->id,
            'activity_log_id' => $id,
            'hidden_at'       => $now,
        ])->all();

        // Upsert handles re-hides idempotently when the user accidentally
        // dismisses something twice across two tabs.
        DB::table('activity_log_hides')->upsert(
            $rows,
            ['user_id', 'activity_log_id'],
            ['hidden_at']
        );

        return response()->json([
            'ok'     => true,
            'hidden' => $ids->count(),
        ]);
    }
}
