// database/migrations/2025_08_02_000009_create_ocupaciones_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOcupacionesTable extends Migration
{
    public function up()
    {
        Schema::create('ocupaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credito_id');
            $table->string('actividad', 100);
            $table->string('nombre_empresa', 100);
            $table->string('calle', 100);
            $table->string('numero', 10);
            $table->string('colonia', 100);
            $table->string('municipio', 100);
            $table->string('telefono', 20);
            $table->string('antiguedad', 20);
            $table->decimal('monto_percibido', 10, 2);
            $table->string('periodo_pago', 20);
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('credito_id')
                  ->references('id')->on('creditos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ocupaciones');
    }
}
