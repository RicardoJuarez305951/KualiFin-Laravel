// database/migrations/2025_08_02_000012_create_informacion_familiares_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInformacionFamiliaresTable extends Migration
{
    public function up()
    {
        Schema::create('informacion_familiares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credito_id');
            $table->string('nombre_conyuge', 100);
            $table->string('celular_conyuge', 20);
            $table->string('actividad_conyuge', 100);
            $table->decimal('ingresos_semanales_conyuge', 10, 2);
            $table->string('domicilio_trabajo_conyuge', 255);
            $table->smallInteger('personas_en_domicilio');
            $table->smallInteger('dependientes_economicos');
            $table->boolean('conyuge_vive_con_cliente');
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('credito_id')
                  ->references('id')->on('creditos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('informacion_familiares');
    }
}
