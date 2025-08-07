// database/migrations/2025_08_02_000005_create_clientes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('promotor_id');
            $table->string('CURP', 18);
            $table->string('nombre', 100);
            $table->string('apellido_p', 100);
            $table->string('apellido_m', 100);
            $table->date('fecha_nacimiento');
            $table->boolean('tiene_credito_activo');
            $table->string('estatus', 100);
            $table->decimal('monto_maximo', 12, 2);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('activo');

            $table->foreign('promotor_id')
                  ->references('id')->on('promotores')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes');
    }
}
