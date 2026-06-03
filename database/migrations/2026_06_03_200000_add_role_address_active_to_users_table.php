<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('subadmin')->after('password');
            $table->text('address')->nullable()->after('role');
            $table->boolean('active')->default(true)->after('address');
        });

        // Treat the originally seeded account(s) as admin so the new role
        // gate doesn't lock the owner out of the dashboard.
        DB::table('users')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'address', 'active']);
        });
    }
};
