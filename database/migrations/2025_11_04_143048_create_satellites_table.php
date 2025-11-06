<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('satellites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('current_x')->default(0);
            $table->integer('current_y')->default(0);
            $table->integer('current_z')->default(0);
            $table->integer('target_x')->nullable();
            $table->integer('target_y')->nullable();
            $table->integer('target_z')->nullable();
            $table->timestamp('arrival_time')->nullable();
            $table->string('status')->default('idle');
            $table->integer('energy')->default(100);
            $table->integer('integrity')->default(100);
            $table->json('malfunctions')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['status', 'arrival_time']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('satellites');
    }
};
