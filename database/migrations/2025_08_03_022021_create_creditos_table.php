// database/migrations/2025_08_02_000006_create_creditos_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditosTable extends Migration
{
    public function up()
    {
        Schema::create('creditos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cliente_id');
            $table->decimal('monto_total', 12, 2);
            $table->string('estado', 20);
            $table->decimal('interes', 5, 2);
            $table->string('periodicidad', 100);
            $table->date('fecha_inicio');
            $table->date('fecha_final');

            $table->foreign('cliente_id')
                  ->references('id')->on('clientes')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('creditos');
    }
}
