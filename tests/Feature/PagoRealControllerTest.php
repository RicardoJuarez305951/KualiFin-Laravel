<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PagoProyectado;

class PagoRealControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

        Schema::dropIfExists('pagos_reales');
        Schema::dropIfExists('pagos_proyectados');

        Schema::create('pagos_proyectados', function (Blueprint $table) {
            $table->id();
            $table->decimal('deuda_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('pagos_reales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_proyectado_id');
            $table->decimal('monto_pagado', 12, 2);
            $table->string('tipo', 100);
            $table->date('fecha_pago');
            $table->timestamps();
        });
    }

    public function test_store_multiple_creates_records()
    {
        $pago1 = PagoProyectado::create(['deuda_total' => 100]);
        $pago2 = PagoProyectado::create(['deuda_total' => 200]);

        $response = $this->postJson('/mobile/promotor/pagos-multiples', [
            'pago_proyectado_ids' => [$pago1->id, $pago2->id],
        ]);

        $response->assertStatus(201);
        $response->assertJsonCount(2);

        $this->assertDatabaseHas('pagos_reales', [
            'pago_proyectado_id' => $pago1->id,
            'monto_pagado' => 100,
            'tipo' => 'completo',
        ]);

        $this->assertDatabaseHas('pagos_reales', [
            'pago_proyectado_id' => $pago2->id,
            'monto_pagado' => 200,
            'tipo' => 'completo',
        ]);
    }
}

