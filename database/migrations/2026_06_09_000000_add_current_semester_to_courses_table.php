<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tracks which semester (or, for boards, which year) the course is
    // currently running. Drives the "Upgrade Semester" tab in Master
    // Data — admin clicks Upgrade and the column bumps along with each
    // enrolled student's semester / course_year.
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'current_semester')) {
                $table->unsignedTinyInteger('current_semester')->default(1)->after('fee_per_sem');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'current_semester')) {
                $table->dropColumn('current_semester');
            }
        });
    }
};
