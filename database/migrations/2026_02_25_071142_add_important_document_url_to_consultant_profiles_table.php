<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consultant_profiles', function (Blueprint $table) {
            $table->string('important_document_url', 500)->nullable()->after('meeting_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultant_profiles', function (Blueprint $table) {
            $table->dropColumn('important_document_url');
        });
    }
};
