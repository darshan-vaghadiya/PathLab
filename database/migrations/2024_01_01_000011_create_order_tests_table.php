<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('test_id')->constrained('tests')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->enum('status', ['pending', 'sample_collected', 'processing', 'completed', 'approved'])->default('pending');
            $table->text('result_value')->nullable();
            $table->text('result_remarks')->nullable();
            $table->timestamp('sample_collected_at')->nullable();
            $table->foreignId('sample_collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('result_entered_at')->nullable();
            $table->foreignId('result_entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tests');
    }
};
