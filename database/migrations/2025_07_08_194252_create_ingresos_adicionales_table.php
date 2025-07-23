<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ingresos_adicionales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ocupacion_id');
            $table->string('concepto', 100);
            $table->decimal('monto', 10, 2);
            $table->string('frecuencia', 20);
            $table->timestamp('creado_en')->nullable();

            $table->foreign('ocupacion_id')->references('id')->on('ocupaciones')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('ingresos_adicionales');
    }
};
