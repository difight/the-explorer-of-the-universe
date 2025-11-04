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
        Schema::create('planets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('star_system_id')->constrained();
            $table->string('tech_name'); // "Planet-1", "Planet-2"
            $table->string('type'); // barren, desert, oceanic, etc
            $table->boolean('has_life')->default(false);
            $table->integer('size');
            $table->float('resource_bonus')->default(1.0);
            $table->json('special_features')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planets');
    }
};
