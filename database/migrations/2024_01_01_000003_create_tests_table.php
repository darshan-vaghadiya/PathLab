<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_category_id')->constrained('test_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('description')->nullable();
            $table->text('normal_range')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('method')->nullable();
            $table->string('sample_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
