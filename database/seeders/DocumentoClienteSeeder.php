<?php

namespace Database\Seeders;

use App\Models\DocumentoCliente;
use App\Models\Cliente;
use App\Models\Credito;
use Illuminate\Database\Seeder;

class DocumentoClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();
        $creditos = Credito::all();

        for ($i = 0; $i < 20; $i++) {
            DocumentoCliente::create([
                'cliente_id' => $clientes->random()->id,
                'credito_id' => $creditos->random()->id,
                'tipo_doc' => fake()->word(),
                'url_s3' => fake()->url(),
                'nombre_arch' => fake()->word().'.pdf',
            ]);
        }
    }
}
