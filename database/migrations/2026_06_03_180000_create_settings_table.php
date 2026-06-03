<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Platform details
            $table->string('platform_name')->nullable();
            $table->string('platform_email')->nullable();
            $table->string('platform_mobile', 20)->nullable();
            $table->string('platform_alt_mobile', 20)->nullable();
            $table->string('website')->nullable();
            $table->string('owner')->nullable();
            $table->text('address')->nullable();

            // Bank details
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_ifsc', 20)->nullable();
            $table->string('bank_account_number', 30)->nullable();
            $table->string('bank_holder_name')->nullable();

            // Branding
            $table->string('logo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
