<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_tests', function (Blueprint $table) {
            $table->dropColumn('result_value');
        });
    }

    public function down(): void
    {
        Schema::table('order_tests', function (Blueprint $table) {
            $table->text('result_value')->nullable()->after('status');
        });
    }
};
