<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('informacion_familiares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credito_id');
            $table->string('nombre_conyuge', 100);
            $table->string('celular_conyuge', 20);
            $table->string('actividad_conyuge', 100);
            $table->decimal('ingresos_semanales_conyuge', 10, 2);
            $table->string('domicilio_trabajo_conyuge', 255);
            $table->smallInteger('numero_hijos');
            $table->smallInteger('personas_en_domicilio');
            $table->smallInteger('dependientes_economicos');
            $table->boolean('conyuge_vive_con_cliente');
            $table->timestamp('creado_en')->nullable();

            $table->foreign('credito_id')->references('id')->on('creditos')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('informacion_familiares');
    }
};
