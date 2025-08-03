// database/migrations/2025_08_02_000002_create_supervisores_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupervisoresTable extends Migration
{
    public function up()
    {
        Schema::create('supervisores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ejecutivo_id');
            $table->string('nombre', 100);
            $table->string('apellido_p', 100);
            $table->string('apellido_m', 100);

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
            $table->foreign('ejecutivo_id')
                  ->references('id')->on('ejecutivos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('supervisores');
    }
}
