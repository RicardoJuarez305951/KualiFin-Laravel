<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                if (!Schema::hasColumn('clientes', 'cartera_estado')) {
                    $table->string('cartera_estado', 100)->default('inactivo');
                }
            });

            if (Schema::hasColumn('clientes', 'estatus')) {
                DB::table('clientes')->update([
                    'cartera_estado' => DB::raw("COALESCE(estatus, 'inactivo')"),
                ]);

                Schema::table('clientes', function (Blueprint $table) {
                    $table->dropColumn('estatus');
                });
            }
        }

        if (Schema::hasTable('creditos') && Schema::hasColumn('creditos', 'cartera_estado')) {
            Schema::table('creditos', function (Blueprint $table) {
                $table->dropColumn('cartera_estado');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('creditos') && !Schema::hasColumn('creditos', 'cartera_estado')) {
            Schema::table('creditos', function (Blueprint $table) {
                $table->string('cartera_estado', 100)->nullable()->after('estado');
            });
        }

        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $table) {
                if (!Schema::hasColumn('clientes', 'estatus')) {
                    $table->string('estatus', 100)->default('inactivo');
                }
            });

            DB::table('clientes')->update([
                'estatus' => DB::raw("COALESCE(cartera_estado, 'inactivo')"),
            ]);

            if (Schema::hasColumn('clientes', 'cartera_estado')) {
                Schema::table('clientes', function (Blueprint $table) {
                    $table->dropColumn('cartera_estado');
                });
            }
        }
    }
};