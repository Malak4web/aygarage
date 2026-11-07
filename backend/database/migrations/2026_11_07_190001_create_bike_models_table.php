<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bike_models', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. 'ZT350-T'
            $table->string('name');           // e.g. 'Zontes 350-T'
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bike_models');
    }
};
