<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('used_bikes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('bike_model_code')->index(); // e.g. 'ZT350-T'
            $table->string('model_name')->nullable();  // e.g. 'Zontes ZT350-T'
            $table->string('family')->nullable();      // e.g. 'ZT350'
            $table->integer('year');
            $table->integer('km');
            $table->integer('price');
            $table->integer('installment_monthly')->default(0);
            $table->integer('inspection_score');
            $table->string('color')->nullable();
            $table->string('owner_label')->nullable();  // e.g. 'أول مالك'
            $table->string('license_until')->nullable(); // e.g. 'مارس 2027'
            $table->text('notes')->nullable();
            $table->json('specs')->nullable();
            $table->string('status')->default('available')->index(); // 'available', 'reserved', 'sold'
            $table->integer('sold_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('used_bikes');
    }
};
