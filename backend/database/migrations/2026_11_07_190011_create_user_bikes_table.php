<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_bikes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('bike_model_code'); // e.g. 'ZT350-T'
            $table->string('model_name')->nullable(); // e.g. 'Zontes ZT350-T'
            $table->integer('year');
            $table->string('plate')->nullable(); // e.g. 'س ط ع 1234'
            $table->integer('odometer')->default(0);
            $table->integer('next_service_km')->default(0);
            $table->string('license_until')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bikes');
    }
};
