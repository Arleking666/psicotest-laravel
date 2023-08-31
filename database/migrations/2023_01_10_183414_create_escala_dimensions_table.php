<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('escala_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_escala')->constrained('escalas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('id_dimension')->constrained('dimensions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('escala_dimensions');
    }
};
