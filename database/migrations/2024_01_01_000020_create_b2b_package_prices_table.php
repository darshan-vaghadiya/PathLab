<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b2b_package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('b2b_client_id')->constrained('b2b_clients')->cascadeOnDelete();
            $table->foreignId('test_package_id')->constrained('test_packages')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['b2b_client_id', 'test_package_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_package_prices');
    }
};
