// database/migrations/2025_08_02_000017_create_comisiones_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComisionesTable extends Migration
{
    public function up()
    {
        Schema::create('comisiones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tipo_beneficiado', 20);
            $table->unsignedBigInteger('beneficiado_id');
            $table->decimal('porcentaje', 5, 2);
            $table->decimal('monto_base', 12, 2);
            $table->decimal('monto_pago', 12, 2);
            $table->date('fecha_pago');
        });
    }

    public function down()
    {
        Schema::dropIfExists('comisiones');
    }
}
