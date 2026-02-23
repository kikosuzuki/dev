<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('consultant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('consultant_schedules')->onDelete('cascade');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['approved', 'cancelled', 'completed'])->default('approved');
            $table->text('notes')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->string('google_event_id')->nullable();
            $table->string('meeting_url')->nullable();
            $table->integer('amount')->default(0);
            $table->boolean('reminder_day_before_sent')->default(false);
            $table->boolean('reminder_day_of_sent')->default(false);
            $table->boolean('reminder_10min_sent')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['consultant_id', 'status']);
            $table->index(['booking_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
