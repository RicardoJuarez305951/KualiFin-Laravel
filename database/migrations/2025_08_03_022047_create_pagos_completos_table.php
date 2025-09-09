// database/migrations/2025_08_03_022047_create_pagos_completos_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosCompletosTable extends Migration
{
    public function up()
    {
        Schema::create('pagos_completos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pago_real_id');
            $table->decimal('monto_completo', 12, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('pago_real_id')
                  ->references('id')->on('pagos_reales')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos_completos');
    }
}

