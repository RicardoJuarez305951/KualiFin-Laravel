<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentosTable extends Migration
{
    public function up()
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('credito_id');
            $table->unsignedBigInteger('promotor_id');
            $table->unsignedBigInteger('supervisor_id');
            $table->unsignedBigInteger('ejecutivo_id');
            $table->unsignedBigInteger('tipo_documento_id');
            $table->date('fecha_generacion');
            $table->string('url_s3', 255);

            $table->foreign('credito_id')
                  ->references('id')->on('creditos')
                  ->onDelete('cascade');
            $table->foreign('promotor_id')
                  ->references('id')->on('promotores')
                  ->onDelete('cascade');
            $table->foreign('supervisor_id')
                  ->references('id')->on('supervisores')
                  ->onDelete('cascade');
            $table->foreign('ejecutivo_id')
                  ->references('id')->on('ejecutivos')
                  ->onDelete('cascade');
            $table->foreign('tipo_documento_id')
                  ->references('id')->on('tipo_documentos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentos');
    }
}
