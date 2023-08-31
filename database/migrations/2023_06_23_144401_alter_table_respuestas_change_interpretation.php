<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('respuestas', function (Blueprint $table) {
            $table->string('interpretation', 4000)->change();
        });
    }

    public function down()
    {
    }
};
