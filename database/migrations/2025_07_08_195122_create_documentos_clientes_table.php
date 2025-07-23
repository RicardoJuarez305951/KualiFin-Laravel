<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('documentos_clientes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('credito_id')->nullable();
            $table->string('tipo_doc', 20); // INE, CURP, DOMICILIO, etc.
            $table->string('url_s3', 255);
            $table->string('nombre_arch', 150);
            $table->timestamp('creado_en')->nullable();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('credito_id')->references('id')->on('creditos')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('documentos_clientes');
    }
};
