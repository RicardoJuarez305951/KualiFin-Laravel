<?php

use App\Enums\ClienteEstado;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ESTADO_INACTIVO = ClienteEstado::INACTIVO->value;

    public function up(): void
    {
        // 1) Instalación limpia: crea la tabla con el esquema final
        if (!Schema::hasTable('clientes')) {
            Schema::create('clientes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('promotor_id');

                $table->string('CURP', 18);
                $table->string('nombre', 100);
                $table->string('apellido_p', 100);
                $table->string('apellido_m', 100);
                $table->date('fecha_nacimiento');
                $table->boolean('tiene_credito_activo');
                $table->string('cliente_estado', 30)->default(self::ESTADO_INACTIVO);
                // Estados
                // 'demanda',          proceso legal / lista negra
                // 'deudor',           deuda fuera de ciclo
                // 'vencido',          deuda dentro del ciclo
                // 'falla',            atraso dentro de crédito activo
                // 'activo',           crédito vigente y al corriente
                // 'venta_registrada', en espera de desembolso
                // 'por_supervisar',   en investigación de campo
                // 'doble_check',      validación documental
                // 'prospecto',        cliente nuevo
                // 'inactivo',         sin crédito ni deuda
                // 'cancelado'         anulado antes de desembolso
                $table->decimal('monto_maximo', 12, 2);

                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
                $table->boolean('activo');

                $table->foreign('promotor_id')
                    ->references('id')->on('promotores')
                    ->onDelete('cascade');
            });
        } else {
            // 2) Proyecto existente: aplicar cambios incrementales en una sola migración
            Schema::table('clientes', function (Blueprint $table) {
                if (!Schema::hasColumn('clientes', 'cliente_estado')) {
                    $table->string('cliente_estado', 100)->default(self::ESTADO_INACTIVO);
                }
            });

            // Migrar datos desde 'estatus' hacia 'cliente_estado' y eliminar 'estatus'
            if (Schema::hasColumn('clientes', 'estatus')) {
                $defaultEstado = self::ESTADO_INACTIVO;
                DB::table('clientes')->update([
                    'cliente_estado' => DB::raw("COALESCE(estatus, '{$defaultEstado}')"),
                ]);

                Schema::table('clientes', function (Blueprint $table) {
                    $table->dropColumn('estatus');
                });
            }
        }

        // 3) Limpieza en creditos: eliminar 'cliente_estado' si todavía existe ahí
        if (Schema::hasTable('creditos') && Schema::hasColumn('creditos', 'cliente_estado')) {
            Schema::table('creditos', function (Blueprint $table) {
                $table->dropColumn('cliente_estado');
            });
        }
    }

    public function down(): void
    {
        // Revertir la limpieza en creditos: volver a crear la columna si no existe
        if (Schema::hasTable('creditos') && !Schema::hasColumn('creditos', 'cliente_estado')) {
            Schema::table('creditos', function (Blueprint $table) {
                // Ajusta la posición 'after' si tu versión de DB lo soporta; si no, quítalo.
                $table->string('cliente_estado', 100)->nullable();
            });
        }

        if (Schema::hasTable('clientes')) {
            // Si la tabla existía antes, intentamos revertir al estado previo:
            if (!Schema::hasColumn('clientes', 'estatus')) {
                Schema::table('clientes', function (Blueprint $table) {
                    $table->string('estatus', 100)->default(self::ESTADO_INACTIVO);
                });
            }

            // Copiar de vuelta los datos si existe 'cliente_estado'
            if (Schema::hasColumn('clientes', 'cliente_estado')) {
                $defaultEstado = self::ESTADO_INACTIVO;
                DB::table('clientes')->update([
                    'estatus' => DB::raw("COALESCE(cliente_estado, '{$defaultEstado}')"),
                ]);

                Schema::table('clientes', function (Blueprint $table) {
                    $table->dropColumn('cliente_estado');
                });
            } else {
                // Si no existía 'cliente_estado' y fue instalación limpia, entonces esta migración creó la tabla
                // En ese caso, eliminamos toda la tabla para revertir completamente.
                Schema::dropIfExists('clientes');
            }
        }
    }
};
