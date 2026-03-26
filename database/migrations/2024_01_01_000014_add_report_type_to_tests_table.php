<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->enum('report_type', ['parametric', 'text', 'mixed'])
                ->default('parametric')
                ->after('sample_type');

            $table->dropColumn(['normal_range', 'unit']);
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('report_type');

            $table->text('normal_range')->nullable()->after('description');
            $table->string('unit')->nullable()->after('normal_range');
        });
    }
};
