<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('public_token', 64)->unique()->nullable()->after('pdf_path');
            $table->timestamp('token_expires_at')->nullable()->after('public_token');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['public_token', 'token_expires_at']);
        });
    }
};
