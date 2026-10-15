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
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('log_id');
            // Nullable so audit entries survive if the user record is ever removed.
            $table->unsignedInteger('user_id')->nullable();
            $table->string('action');
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            $table->index('logged_at');
            $table->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
