<?php

use App\Enums\CreditoEstado;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditosTable extends Migration
{
    public function up(): void
    {
        Schema::create('creditos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cliente_id');
            $table->decimal('monto_total', 12, 2);
            $table->string('estado', 30)->default(CreditoEstado::PROSPECTADO->value);
            // Estados mapeados en CreditoEstado:
            // - prospectado: datos capturados, aún no validados
            // - prospectado_recredito: cliente existente solicitando nuevo crédito
            // - solicitado: solicitud en revisión
            // - aprobado: autorización completa, previo a supervisión
            // - supervisado: verificación de campo terminada
            // - desembolsado: crédito entregado y en curso
            // - activo: crédito vigente y al corriente
            // - vencido: crédito con atraso dentro del ciclo
            // - liquidado: crédito pagado en su totalidad
            // - cancelado: crédito anulado antes o durante el desembolso

            $table->decimal('interes', 5, 2);
            $table->string('periodicidad', 100);
            $table->date('fecha_inicio');
            $table->date('fecha_final');
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamp('cancelado_en')->nullable();
            $table->unsignedBigInteger('cancelado_por_id')->nullable();

            $table->foreign('cliente_id')
                ->references('id')->on('clientes')
                ->onDelete('cascade');
            $table->foreign('cancelado_por_id')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creditos');
    }
}
