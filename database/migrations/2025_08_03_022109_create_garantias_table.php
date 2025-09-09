// database/migrations/2025_08_02_000016_create_garantias_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGarantiasTable extends Migration
{
    public function up()
    {
        Schema::create('garantias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credito_id');
            $table->string('propietario', 100);
            $table->string('tipo', 100);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('num_serie', 100)->nullable();
            $table->string('antiguedad', 20);
            $table->decimal('monto_garantizado', 12, 2);
            $table->string('foto_url', 255);
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('credito_id')
                  ->references('id')->on('creditos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('garantias');
    }
}
