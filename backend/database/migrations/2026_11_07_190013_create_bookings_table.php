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
            $table->string('number')->unique(); // 'BK-1042'
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_bike_id')->nullable()->constrained('user_bikes')->nullOnDelete();
            $table->string('custom_model')->nullable();
            $table->integer('odometer')->nullable();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->date('date');
            $table->string('time'); // e.g. '11:00 AM' or '3:00 العصر'
            $table->integer('price_snapshot')->nullable();
            $table->string('status')->default('pending')->index(); // 'pending', 'confirmed', 'done', 'cancelled'
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['date', 'time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
