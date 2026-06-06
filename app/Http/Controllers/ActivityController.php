<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
}
