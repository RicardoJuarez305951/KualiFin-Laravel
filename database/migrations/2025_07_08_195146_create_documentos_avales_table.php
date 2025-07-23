<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('documentos_avales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('aval_id');
            $table->string('tipo_doc', 20); // INE, CURP, DOMICILIO, etc.
            $table->string('url_s3', 255);
            $table->string('nombre_arch', 150);
            $table->timestamp('creado_en')->nullable();

            $table->foreign('aval_id')->references('id')->on('avales')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('documentos_avales');
    }
};
