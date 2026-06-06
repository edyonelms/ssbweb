<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Link a student to the university (or board) they're enrolled
            // with, and the specific course inside it. Both nullable so
            // older student rows from before the Pay Fee flow stay valid
            // — they just won't surface in the Pay Fee picker until edited.
            $table->foreignId('university_id')->nullable()->after('class_name')
                ->constrained('universities')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->after('university_id')
                ->constrained('courses')->nullOnDelete();

            $table->index('university_id');
            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->dropForeign(['course_id']);
            $table->dropColumn(['university_id', 'course_id']);
        });
    }
};
