<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultant_profiles', function (Blueprint $table) {
            $table->json('google_conflict_calendar_ids')->nullable()->after('google_calendar_id');
        });
    }

    public function down(): void
    {
        Schema::table('consultant_profiles', function (Blueprint $table) {
            $table->dropColumn('google_conflict_calendar_ids');
        });
    }
};
