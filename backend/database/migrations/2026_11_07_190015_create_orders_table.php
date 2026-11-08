<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // '1042'
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('new')->index(); // 'new', 'preparing', 'shipped', 'ready_for_pickup', 'delivered', 'cancelled'
            $table->integer('subtotal');
            $table->integer('discount')->default(0);
            $table->integer('delivery_fee')->default(0);
            $table->integer('total');
            $table->string('coupon_code')->nullable();
            $table->string('delivery_method')->default('home'); // 'home', 'pickup'
            $table->string('payment_method')->default('cod');   // 'cod', 'card', 'vodafone', 'instapay'
            $table->string('payment_status')->default('pending'); // 'pending', 'paid'
            $table->string('name');
            $table->string('phone');
            $table->string('governorate')->nullable();
            $table->string('area')->nullable();
            $table->string('street')->nullable();
            $table->date('delivery_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
