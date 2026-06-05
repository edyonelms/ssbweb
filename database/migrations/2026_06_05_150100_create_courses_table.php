<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('mode', 30)->nullable();         // regular / distance / online
            $table->decimal('duration_years', 4, 1);        // e.g. 2.0, 3.5
            $table->boolean('lateral_entry')->default(false);
            $table->text('subjects')->nullable();           // comma-separated
            $table->timestamps();

            $table->index('university_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
