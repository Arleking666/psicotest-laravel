<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::statement('ALTER TABLE tests ALTER COLUMN autor TYPE bigint USING 1');

        Schema::table('tests', function (Blueprint $table) {
            $table->foreign('autor')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }
};
