// database/migrations/2025_08_02_000019_create_inversiones_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInversionesTable extends Migration
{
    public function up()
    {
        Schema::create('inversiones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('promotora_id');
            $table->decimal('monto_solicitado', 12, 2);
            $table->decimal('monto_aprobado', 12, 2);
            $table->date('fecha_solicitud');
            $table->date('fecha_aprobacion');

            $table->foreign('promotora_id')
                  ->references('id')->on('promotoras')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inversiones');
    }
}
