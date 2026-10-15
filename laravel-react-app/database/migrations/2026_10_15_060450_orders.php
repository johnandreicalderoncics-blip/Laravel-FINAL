<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id');
            $table->unsignedInteger('user_id');
            $table->date('order_date')->index();
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->enum('order_status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending')->index();
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('users')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
