<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Course-level fees moved up from fee_structures so the course
            // form is the single source of truth — Registration Fee and
            // per-Semester Fee live on the course itself.
            $table->decimal('registration_fee', 12, 2)->default(0)->after('duration_years');
            $table->decimal('fee_per_sem', 12, 2)->default(0)->after('registration_fee');
        });

        // Backfill the new course columns from whatever the linked
        // fee_structure already has, so existing data isn't lost when the
        // Fee Structure form drops its fee input.
        if (Schema::hasTable('fee_structures')) {
            $rows = DB::table('fee_structures')->get(['course_id', 'fee_per_sem']);
            foreach ($rows as $row) {
                DB::table('courses')
                    ->where('id', $row->course_id)
                    ->update(['fee_per_sem' => $row->fee_per_sem]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['registration_fee', 'fee_per_sem']);
        });
    }
};
