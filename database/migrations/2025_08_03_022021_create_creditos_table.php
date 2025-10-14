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
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamp('cancelado_en')->nullable();
            $table->unsignedBigInteger('cancelado_por_id')->nullable();

            $table->foreign('cliente_id')
                ->references('id')->on('clientes')
                ->onDelete('cascade');
            $table->foreign('cancelado_por_id')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('creditos');
    }
}
