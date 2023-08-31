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
        Schema::create('puntuacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pregunta')->constrained('preguntas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('id_reactivo')->constrained('reactivos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('asignado');
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
        Schema::dropIfExists('puntuacions');
    }
};
