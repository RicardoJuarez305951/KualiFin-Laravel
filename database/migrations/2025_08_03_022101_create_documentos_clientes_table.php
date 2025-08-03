// database/migrations/2025_08_02_000014_create_documentos_clientes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentosClientesTable extends Migration
{
    public function up()
    {
        Schema::create('documentos_clientes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('credito_id');
            $table->string('tipo_doc', 20);
            $table->string('url_s3', 255);
            $table->string('nombre_arch', 150);
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('cliente_id')
                  ->references('id')->on('clientes')
                  ->onDelete('cascade');
            $table->foreign('credito_id')
                  ->references('id')->on('creditos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentos_clientes');
    }
}
