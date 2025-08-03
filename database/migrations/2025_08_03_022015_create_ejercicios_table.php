// database/migrations/2025_08_02_000003_create_ejercicios_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEjerciciosTable extends Migration
{
    public function up()
    {
        Schema::create('ejercicios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supervisor_id');
            $table->unsignedBigInteger('ejecutivo_id');
            $table->date('fecha_inicio');
            $table->date('fecha_final');
            $table->decimal('dinero', 12, 2);

            $table->foreign('supervisor_id')
                  ->references('id')->on('supervisores')
                  ->onDelete('cascade');
            $table->foreign('ejecutivo_id')
                  ->references('id')->on('ejecutivos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ejercicios');
    }
}
