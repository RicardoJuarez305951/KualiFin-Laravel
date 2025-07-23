<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('garantias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credito_id');
            $table->string('tipo', 100);
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->string('num_serie', 100);
            $table->string('antiguedad', 20);
            $table->string('foto_url', 255);
            $table->timestamp('creado_en')->nullable();

            $table->foreign('credito_id')->references('id')->on('creditos')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('garantias');
    }
};
