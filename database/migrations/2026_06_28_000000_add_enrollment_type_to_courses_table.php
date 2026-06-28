<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Records which "type" a course belongs to. Universities use
    // online / odl; boards use fresh_board / toc / part — mirrors the
    // enrollment_type captured on the student admission form.
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'enrollment_type')) {
                $table->string('enrollment_type', 20)->nullable()->after('mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'enrollment_type')) {
                $table->dropColumn('enrollment_type');
            }
        });
    }
};
