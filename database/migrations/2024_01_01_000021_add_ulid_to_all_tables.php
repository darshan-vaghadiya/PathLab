<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'users', 'test_categories', 'tests', 'test_packages',
            'referring_doctors', 'b2b_clients', 'patients', 'orders',
            'order_tests', 'reports', 'test_parameters', 'parameter_ranges',
            'result_templates', 'b2b_test_prices', 'b2b_package_prices', 'lab_settings',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->ulid('ulid')->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'users', 'test_categories', 'tests', 'test_packages',
            'referring_doctors', 'b2b_clients', 'patients', 'orders',
            'order_tests', 'reports', 'test_parameters', 'parameter_ranges',
            'result_templates', 'b2b_test_prices', 'b2b_package_prices', 'lab_settings',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('ulid');
            });
        }
    }
};
