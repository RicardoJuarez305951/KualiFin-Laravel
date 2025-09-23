<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kanban;

class KanbanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Esto limpia la tabla antes de insertar los nuevos datos para evitar duplicados.
        Kanban::truncate();

        $tasks = [
            // Módulo Promotor
            ['module' => 'Promotor', 'functionality' => 'Objetivo', 'content' => 'Cambiar texto "Mi Objetivo P/Ejecutivo" > "Mi Objetivo P/Ejercicio".', 'assigned' => 'Adair'],
            ['module' => 'Promotor', 'functionality' => 'Objetivo', 'content' => 'Hacerlo funcional -> Datos alimentados de las ventas en la semana.', 'assigned' => 'Ricardo'],
            ['module' => 'Promotor', 'functionality' => 'Objetivo', 'content' => 'Frase inspiracional dependiendo que tan cerca este del objetivo.', 'assigned' => 'Adair'],
            ['module' => 'Promotor', 'functionality' => 'Cartera', 'content' => 'Pagos múltiples: Manejar tanto pagos completos como diferidos.', 'assigned' => 'Ricardo'],
            ['module' => 'Promotor', 'functionality' => 'Venta', 'content' => 'Agregar en el título la fecha en que se da la venta.', 'assigned' => 'Adair'],

            // Módulo Supervisor
            ['module' => 'Supervisor', 'functionality' => 'Objetivo', 'content' => 'Botones Funcionales > Redirigen al promotor en cuestión.', 'assigned' => 'Ricardo'],
            ['module' => 'Supervisor', 'functionality' => 'Cartera', 'content' => 'Agregar barra progreso por promotor, porcentajes y dinero.', 'assigned' => 'Adair'],
            ['module' => 'Supervisor', 'functionality' => 'Venta.Horarios', 'content' => 'Permitir el manejo de Horarios (Promotor.DiasDePago y Cliente.HorarioDePago)', 'assigned' => 'Ricardo'],
            ['module' => 'Supervisor', 'functionality' => 'Venta.Prospectados', 'content' => 'Agregar botón de actualizar y regresar.', 'assigned' => 'Adair'],
            ['module' => 'Supervisor', 'functionality' => 'Venta.Prospectados', 'content' => 'Check de supervisor debe mostrar: Información que ingresó la promotora', 'assigned' => 'Ricardo'],
            ['module' => 'Supervisor', 'functionality' => 'Venta.Supervisar', 'content' => 'Mover formulario actual de Prospectados en Supervisar.', 'assigned' => 'Ricardo'],
            ['module' => 'Supervisor', 'functionality' => 'Búsqueda', 'content' => 'Permitir búsqueda de campos exactos como: Nombre, Dirección, Teléfono', 'assigned' => 'Adair'],

            // Módulo Ejecutivo
            ['module' => 'Ejecutivo', 'functionality' => 'Supervisor.Venta', 'content' => 'Agregar el botón "V" para ir a "Desembolso" que genera un formato por promotor para todos sus clientes.', 'assigned' => 'Ricardo'],

            // Módulo Desembolso
            ['module' => 'Desembolso', 'functionality' => 'Fallo', 'content' => 'Usuario puede seleccionar los clientes, recuperar fecha y agregar falla.', 'assigned' => 'Ricardo'],
            ['module' => 'Desembolso', 'functionality' => 'Préstamo', 'content' => 'Todo se saca del sistema. Agregar botón de Confirmación y Cancelación.', 'assigned' => 'Adair'],
            ['module' => 'Desembolso', 'functionality' => 'Cobranza Semanal', 'content' => 'Diseñar UI para Cobranza Semanal (Tentativo).', 'assigned' => 'Adair'],
            ['module' => 'Desembolso', 'functionality' => 'Adelantos/Recuperación', 'content' => 'Usuario selecciona el cliente y agrega sus adelantos o recuperaciones.', 'assigned' => 'Ricardo'],
            ['module' => 'Desembolso', 'functionality' => 'Automáticos', 'content' => 'Desembolso Real: Llenado automático.', 'assigned' => 'Ricardo'],
            ['module' => 'Desembolso', 'functionality' => 'Automáticos', 'content' => 'Recréditos: Llenado automático.', 'assigned' => 'Ricardo'],
            ['module' => 'Desembolso', 'functionality' => 'Firmas', 'content' => 'Canvas para firmas (Promotor, Supervisor, Ejecutivo) y/o firma electrónica.', 'assigned' => 'Adair'],

            // Generales
            ['module' => 'General', 'functionality' => 'Menú Principal', 'content' => 'Ciclar frase inspiracional en el menú principal.', 'assigned' => 'Adair'],
            ['module' => 'General', 'functionality' => 'UI/UX', 'content' => 'Estandarizar todos los textos a Español.', 'assigned' => 'Adair'],
            ['module' => 'General', 'functionality' => 'Pagos', 'content' => 'Sistema de pagos de colores: Verde(Corriente), Naranja(En Tiempo), Rojo(Falla), Azul(Adelanto).', 'assigned' => 'Adair'],
            ['module' => 'General', 'functionality' => 'UI/UX', 'content' => 'Implementar mensajes de confirmación en acciones críticas.', 'assigned' => 'Ricardo'],
            ['module' => 'General', 'functionality' => 'Filtros', 'content' => 'Listado de filtros, con fórmulas y los datos que ocupan (Pendiente con Iván).', 'assigned' => 'Ricardo'],
            ['module' => 'General', 'functionality' => 'Aperturas', 'content' => 'Hacer que las aperturas sean opcionales.', 'assigned' => 'Ricardo'],
        ];

        foreach ($tasks as $index => $taskData) {
            Kanban::create([
                'content'       => $taskData['content'],
                'module'        => $taskData['module'],
                'functionality' => $taskData['functionality'],
                'assigned'      => $taskData['assigned'],
                'status'        => 'todo',
                'order'         => $index
            ]);
        }
    }
}

