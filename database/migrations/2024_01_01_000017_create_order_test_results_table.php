<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_test_id')->constrained('order_tests')->cascadeOnDelete();
            $table->foreignId('test_parameter_id')->constrained('test_parameters')->cascadeOnDelete();
            $table->text('result_value')->nullable();
            $table->timestamps();

            $table->unique(['order_test_id', 'test_parameter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_test_results');
    }
};
