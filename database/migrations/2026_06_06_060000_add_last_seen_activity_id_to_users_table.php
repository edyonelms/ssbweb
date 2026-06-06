<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Highest activity_logs.id this user has seen in their Recent
            // Activity panel. Anything newer counts as unread in the bell
            // badge. Nullable so existing users default to "seen nothing"
            // until they open the panel once.
            $table->unsignedBigInteger('last_seen_activity_id')->nullable()->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_activity_id');
        });
    }
};
