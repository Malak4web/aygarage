<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('used_bike_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('used_bike_id')->constrained('used_bikes')->cascadeOnDelete();
            $table->string('path');
            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index(['used_bike_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('used_bike_images');
    }
};
