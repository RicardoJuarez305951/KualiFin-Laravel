// database/migrations/2025_08_02_000013_create_avales_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvalesTable extends Migration
{
    public function up()
    {
        Schema::create('avales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('CURP', 18);
            $table->unsignedBigInteger('credito_id');
            $table->string('nombre', 100);
            $table->string('apellido_p', 100);
            $table->string('apellido_m', 100);
            $table->date('fecha_nacimiento');
            $table->string('direccion', 255);
            $table->string('telefono', 20);
            $table->string('parentesco', 20);
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('credito_id')
                  ->references('id')->on('creditos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('avales');
    }
}
