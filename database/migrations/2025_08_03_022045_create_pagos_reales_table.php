// database/migrations/2025_08_02_000008_create_pagos_reales_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosRealesTable extends Migration
{
    public function up()
    {
        Schema::create('pagos_reales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pago_proyectado_id');
            $table->decimal('monto_pagado', 12, 2);
            $table->string('tipo', 100);
            $table->date('fecha_pago');
            $table->text('comentario')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('pago_proyectado_id')
                  ->references('id')->on('pagos_proyectados')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos_reales');
    }
}
