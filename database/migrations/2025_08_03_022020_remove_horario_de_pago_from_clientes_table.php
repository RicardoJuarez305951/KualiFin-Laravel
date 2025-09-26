<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('clientes', 'horario_de_pago')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropColumn('horario_de_pago');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('clientes', 'horario_de_pago')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->string('horario_de_pago', 5)->nullable();
            });
        }
    }
};
