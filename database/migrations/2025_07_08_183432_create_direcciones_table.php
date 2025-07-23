<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('direcciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credito_id');
            $table->string('calle', 150);
            $table->string('numero_ext', 10);
            $table->string('numero_int', 10)->nullable();
            $table->integer('monto_mensual');
            $table->string('colonia', 100);
            $table->string('municipio', 100);
            $table->string('estado', 100);
            $table->string('cp', 10);
            $table->string('tiempo_residencia', 20);
            $table->string('tel_fijo', 20);
            $table->string('tel_cel', 20);
            $table->string('tipo_de_vivienda', 100);
            $table->timestamp('creado_en')->nullable();

            $table->foreign('credito_id')->references('id')->on('creditos')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('direcciones');
    }
};
