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
        Schema::create('respuestas', function (Blueprint $table) {
            $table->id();
            $table->string('email_user');
            $table->foreign('email_user')->references('email')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('id_docente_test')->constrained('docente_tests')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('estado');                                      
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
        Schema::dropIfExists('respuestas');
    }
};
