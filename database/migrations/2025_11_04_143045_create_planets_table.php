<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('planets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('star_system_id')->constrained()->onDelete('cascade');
            $table->string('tech_name');
            $table->string('type');
            $table->boolean('has_life')->default(false);
            $table->integer('size');
            $table->float('resource_bonus')->default(1.0);
            $table->json('special_features')->nullable();
            $table->integer('orbit_distance');
            $table->float('temperature')->default(0.0);
            $table->timestamps();

            $table->index(['star_system_id', 'type']);
            $table->index(['has_life']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('planets');
    }
};
