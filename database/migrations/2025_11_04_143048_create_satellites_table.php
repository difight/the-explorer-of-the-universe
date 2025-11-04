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
        Schema::create('satellites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name');
            $table->integer('current_x')->default(0);
            $table->integer('current_y')->default(0);
            $table->integer('current_z')->default(0);
            $table->integer('target_x')->nullable();
            $table->integer('target_y')->nullable();
            $table->integer('target_z')->nullable();
            $table->timestamp('arrival_time')->nullable();
            $table->string('status')->default('idle'); // idle, traveling, damaged
            $table->integer('fuel')->default(100);
            $table->integer('integrity')->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satellites');
    }
};
