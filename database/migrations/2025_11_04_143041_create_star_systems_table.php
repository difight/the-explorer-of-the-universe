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
        Schema::create('star_systems', function (Blueprint $table) {
            $table->id();
            $table->integer('coord_x');
            $table->integer('coord_y');
            $table->integer('coord_z');
            $table->string('name'); // "Sector-X-Y-Z"
            $table->boolean('is_generated')->default(false);
            $table->timestamps();

            $table->unique(['coord_x', 'coord_y', 'coord_z']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('star_systems');
    }
};
