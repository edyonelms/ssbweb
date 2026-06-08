<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_name', 255)->nullable()->after('address');
            $table->string('bank_branch', 255)->nullable()->after('bank_name');
            $table->string('bank_ifsc', 20)->nullable()->after('bank_branch');
            $table->string('bank_account_number', 30)->nullable()->after('bank_ifsc');
            $table->string('bank_holder_name', 255)->nullable()->after('bank_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_branch',
                'bank_ifsc',
                'bank_account_number',
                'bank_holder_name',
            ]);
        });
    }
};
