<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('discoveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('planet_id')->constrained()->onDelete('cascade');
            $table->string('custom_name')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamp('discovered_at');
            $table->timestamps();

            $table->unique(['planet_id', 'user_id']);
            $table->index(['user_id', 'discovered_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('discoveries');
    }
};
