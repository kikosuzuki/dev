<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('chatwork_id', 100)->nullable()->after('line_user_id');
            $table->string('chatwork_room_id', 100)->nullable()->after('chatwork_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['chatwork_id', 'chatwork_room_id']);
        });
    }
};
