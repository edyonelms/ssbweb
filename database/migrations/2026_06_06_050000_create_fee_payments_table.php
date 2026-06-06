<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            // 0 = registration fee, 1..N = semester index.
            $table->unsignedSmallInteger('semester');
            $table->decimal('amount', 12, 2);
            $table->string('mode', 16);
            $table->string('collected_by_name');
            $table->string('remark', 500)->nullable();
            // The user (admin / sub-admin) who recorded the payment.
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            // Receipts entered in one "Pay Fee" submission share this id so
            // the overflow split (1 paid → 1000 sem 1 + 1000 sem 2) reads as
            // a single transaction in the history.
            $table->uuid('batch_id')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->index(['student_id', 'semester']);
            $table->index('paid_at');
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
