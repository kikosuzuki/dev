<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('consultation_result', ['success', 'failure', 'pending'])->nullable()->after('cancel_reason');
            $table->text('consultation_notes')->nullable()->after('consultation_result');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['consultation_result', 'consultation_notes']);
        });
    }
};
