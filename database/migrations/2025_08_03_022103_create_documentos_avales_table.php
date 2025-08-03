// database/migrations/2025_08_02_000015_create_documentos_avales_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentosAvalesTable extends Migration
{
    public function up()
    {
        Schema::create('documentos_avales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('aval_id');
            $table->string('tipo_doc', 20);
            $table->string('url_s3', 255);
            $table->string('nombre_arch', 150);
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('aval_id')
                  ->references('id')->on('avales')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentos_avales');
    }
}
