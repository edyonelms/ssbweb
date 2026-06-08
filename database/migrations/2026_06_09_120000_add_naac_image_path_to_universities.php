<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            // Accreditation badge (NAAC / NIRF / UGC etc.) shown next to
            // the main logo on the admission-form template. Optional —
            // kept null when the university hasn't uploaded one.
            $table->string('naac_image_path')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn('naac_image_path');
        });
    }
};
