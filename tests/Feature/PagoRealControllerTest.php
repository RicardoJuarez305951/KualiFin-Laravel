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

        Schema::dropIfExists('pagos_diferidos');
        Schema::dropIfExists('pagos_completos');
        Schema::dropIfExists('pagos_reales');
        Schema::dropIfExists('pagos_proyectados');

        Schema::create('pagos_proyectados', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto_proyectado', 12, 2)->default(0);
            $table->decimal('deuda_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('pagos_reales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_proyectado_id');
            $table->string('tipo', 100);
            $table->date('fecha_pago');
            $table->text('comentario')->nullable();
            $table->timestamps();
        });

        Schema::create('pagos_completos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_real_id');
            $table->decimal('monto_completo', 12, 2);
            $table->timestamps();
        });

        Schema::create('pagos_diferidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_real_id');
            $table->decimal('monto_diferido', 12, 2);
            $table->timestamps();
        });
    }

    public function test_store_multiple_creates_complete_and_deferred_payments()
    {
        $pago1 = PagoProyectado::create([
            'monto_proyectado' => 120,
            'deuda_total' => 180.75,
        ]);
        $pago2 = PagoProyectado::create([
            'monto_proyectado' => 200,
            'deuda_total' => 210,
        ]);

        $this->withoutMiddleware();

        $response = $this->postJson('/mobile/promotor/pagos-multiples', [
            'pagos' => [
                [
                    'pago_proyectado_id' => $pago1->id,
                    'tipo' => 'completo',
                    'monto' => 0,
                ],
                [
                    'pago_proyectado_id' => $pago2->id,
                    'tipo' => 'diferido',
                    'monto' => 55.75,
                ],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonCount(2);

        $payload = $response->json();

        $this->assertEquals('completo', $payload[0]['tipo']);
        $this->assertEquals($pago1->id, $payload[0]['pago_proyectado_id']);
        $this->assertEquals(180.75, (float) ($payload[0]['pago_completo']['monto_completo'] ?? 0));

        $this->assertEquals('diferido', $payload[1]['tipo']);
        $this->assertEquals($pago2->id, $payload[1]['pago_proyectado_id']);
        $this->assertEquals(55.75, (float) ($payload[1]['pago_diferido']['monto_diferido'] ?? 0));

        $this->assertDatabaseHas('pagos_reales', [
            'pago_proyectado_id' => $pago1->id,
            'tipo' => 'completo',
        ]);

        $this->assertDatabaseHas('pagos_completos', [
            'monto_completo' => 180.75,
        ]);

        $this->assertDatabaseHas('pagos_reales', [
            'pago_proyectado_id' => $pago2->id,
            'tipo' => 'diferido',
        ]);

        $this->assertDatabaseHas('pagos_diferidos', [
            'monto_diferido' => 55.75,
        ]);
    }
}

