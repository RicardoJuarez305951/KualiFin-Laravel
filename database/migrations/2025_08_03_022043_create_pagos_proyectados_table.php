// database/migrations/2025_08_02_000007_create_pagos_proyectados_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosProyectadosTable extends Migration
{
    public function up()
    {
        Schema::create('pagos_proyectados', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credito_id');
            $table->smallInteger('semana');
            $table->decimal('monto_proyectado', 12, 2);
            $table->date('fecha_limite');
            $table->string('estado', 20);

            $table->foreign('credito_id')
                  ->references('id')->on('creditos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos_proyectados');
    }
}
