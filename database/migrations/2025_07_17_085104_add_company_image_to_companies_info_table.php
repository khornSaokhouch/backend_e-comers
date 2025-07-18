<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies_info', function (Blueprint $table) {
            $table->string('company_image')->nullable()->after('company_name'); // adjust position if needed
        });
    }

    public function down(): void
    {
        Schema::table('companies_info', function (Blueprint $table) {
            $table->dropColumn('company_image');
        });
    }
};

