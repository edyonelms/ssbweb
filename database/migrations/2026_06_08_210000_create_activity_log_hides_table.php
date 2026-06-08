<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Per-user "I dismissed this from my activity feed" pivot. The
    // underlying activity_logs row stays put so other users still see
    // their copy of the entry — handy for admin notifications fanned out
    // to multiple sub-admins, where each can independently clear theirs.
    public function up(): void
    {
        Schema::create('activity_log_hides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_log_id')->constrained('activity_logs')->cascadeOnDelete();
            $table->timestamp('hidden_at')->useCurrent();
            $table->unique(['user_id', 'activity_log_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log_hides');
    }
};
