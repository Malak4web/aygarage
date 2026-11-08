<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('used_bike_service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('used_bike_id')->constrained('used_bikes')->cascadeOnDelete();
            $table->date('date');
            $table->integer('odometer');
            $table->string('work');
            $table->string('technician');
            $table->timestamps();

            $table->index(['used_bike_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('used_bike_service_records');
    }
};
