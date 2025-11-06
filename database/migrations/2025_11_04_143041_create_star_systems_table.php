<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('star_systems', function (Blueprint $table) {
            $table->id();
            $table->integer('coord_x');
            $table->integer('coord_y');
            $table->integer('coord_z');
            $table->string('name');
            $table->string('star_type')->default('G');
            $table->float('star_mass')->default(1.0);
            $table->boolean('is_generated')->default(false);
            $table->boolean('is_start_system')->default(false);
            $table->timestamps();

            $table->unique(['coord_x', 'coord_y', 'coord_z']);
            $table->index(['coord_x', 'coord_y', 'coord_z']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('star_systems');
    }
};
