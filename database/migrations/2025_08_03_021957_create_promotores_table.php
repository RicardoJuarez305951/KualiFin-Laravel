// database/migrations/2025_08_02_000004_create_promotores_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotoresTable extends Migration
{
    public function up()
    {
        Schema::create('promotores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('supervisor_id');
            $table->string('nombre', 100);
            $table->string('apellido_p', 100);
            $table->string('apellido_m', 100);
            $table->decimal('venta_maxima', 12, 2);
            $table->string('colonia', 100);
            $table->decimal('venta_proyectada_objetivo', 12, 2);
            $table->decimal('bono', 12, 2);
            $table->string('dias_de_pago', 100)
                  ->nullable()
                  ->comment('Días en los que el promotor realiza cobros programados');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
            $table->foreign('supervisor_id')
                  ->references('id')->on('supervisores')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotores');
    }
}
