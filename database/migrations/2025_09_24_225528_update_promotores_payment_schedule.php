<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotores', function (Blueprint $table) {
            $table->string('dia_de_pago', 50)
                ->nullable()
                ->after('bono');
            $table->time('hora_de_pago')
                ->nullable()
                ->after('dia_de_pago');
        });

        Schema::table('promotores', function (Blueprint $table) {
            $table->dropColumn('dias_de_pago');
        });
    }

    public function down(): void
    {
        Schema::table('promotores', function (Blueprint $table) {
            $table->string('dias_de_pago', 100)
                ->nullable()
                ->comment('Días en los que el promotor realiza cobros programados')
                ->after('bono');
            $table->dropColumn(['dia_de_pago', 'hora_de_pago']);
        });
    }
};
