<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pagos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credito_id');
            $table->decimal('monto', 12, 2);
            $table->date('fecha_pago');
            $table->string('tipo_pago', 20);
            $table->timestamp('creado_en')->nullable();

            $table->foreign('credito_id')->references('id')->on('creditos')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('pagos');
    }
};
