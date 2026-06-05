<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 20);
            $table->string('email')->nullable();
            $table->string('admission_no', 50)->nullable();
            $table->string('class_name', 50)->nullable();
            $table->string('gender', 10)->nullable();   // male / female / other
            $table->string('parent_name')->nullable();
            $table->text('address')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_by');
            $table->index('class_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
