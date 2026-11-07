<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // 's1', 's2', ...
            $table->string('name');           // 'تغيير زيت وفلتر'
            $table->string('duration_label'); // '45 دقيقة'
            $table->integer('price')->nullable(); // null or 1770
            $table->string('price_label')->nullable(); // 'حسب القطع' or null
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
