<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('brand');
            $table->string('sku')->index();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('type')->index(); // 'oil', 'filter', 'brake', etc.
            $table->string('color')->nullable();
            $table->integer('price');
            $table->integer('old_price')->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('rating', 3, 1)->default(5.0);
            $table->integer('review_count')->default(0);
            $table->integer('sold_count')->default(0);
            $table->string('badge')->nullable(); // e.g. 'الأكثر مبيعاً', 'جديد'
            $table->boolean('is_best_seller')->default(false)->index();
            $table->boolean('is_weekly_offer')->default(false)->index();
            $table->string('viscosity')->nullable()->index(); // e.g. '10W-40', '15W-50'
            $table->string('oil_type')->nullable(); // e.g. 'تخليقي 100%'
            $table->string('fit_note')->nullable();
            $table->text('description')->nullable();
            $table->json('specs')->nullable();
            $table->json('fits')->nullable(); // array of bike model codes: ['ZT350-T', ...]
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
