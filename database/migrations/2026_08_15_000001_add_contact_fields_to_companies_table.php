<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('contact_phone', 50)->nullable()->after('name');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->string('contact_address', 500)->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['contact_phone', 'contact_email', 'contact_address']);
        });
    }
};