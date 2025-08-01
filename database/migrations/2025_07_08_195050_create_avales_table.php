<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('avales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credito_id');
            $table->string('nombre', 100);
            $table->string("apellido_p", 100);
            $table->string("apellido_m", 100);
            $table->string('curp', 18)->unique();
            $table->string('direccion', 255);
            $table->string('telefono', 20);
            $table->string('parentesco', 20);
            $table->timestamp('creado_en')->nullable();

            $table->foreign('credito_id')->references('id')->on('creditos')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('avales');
    }
};
