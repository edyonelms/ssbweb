<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('heading');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_original_name')->nullable();
            $table->string('audience', 20)->default('all'); // 'all' or 'selected'
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('announcement_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->unique(['announcement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_user');
        Schema::dropIfExists('announcements');
    }
};
