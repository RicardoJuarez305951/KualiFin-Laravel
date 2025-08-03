// database/migrations/2025_08_02_000010_create_ingresos_adicionales_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngresosAdicionalesTable extends Migration
{
    public function up()
    {
        Schema::create('ingresos_adicionales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ocupacion_id');
            $table->string('concepto', 100);
            $table->decimal('monto', 10, 2);
            $table->string('frecuencia', 20);
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('ocupacion_id')
                  ->references('id')->on('ocupaciones')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ingresos_adicionales');
    }
}
