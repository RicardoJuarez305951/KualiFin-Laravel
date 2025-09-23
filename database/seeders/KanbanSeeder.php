<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kanban;

class KanbanSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            // Módulo Promotor
            ['content' => 'Programar cálculo de Mi Objetivo P/Ejercicio.', 'module' => 'Promotor', 'functionality' => 'Objetivos', 'assigned' => 'Ricardo'],
            ['content' => 'Conectar ventas registradas con las ventas semanales de la promotora.', 'module' => 'Promotor', 'functionality' => 'Objetivos', 'assigned' => 'Ricardo'],
            ['content' => 'Ajustar cálculo para considerar semanas de paro.', 'module' => 'Promotor', 'functionality' => 'Objetivos', 'assigned' => 'Ricardo'],
            ['content' => 'Generar frase inspiracional según % de objetivo alcanzado.', 'module' => 'Promotor', 'functionality' => 'Motivación', 'assigned' => 'Adair'],
            ['content' => 'Implementar carrusel/ciclado de frases en el menú principal.', 'module' => 'Promotor', 'functionality' => 'Motivación', 'assigned' => 'Adair'],
            ['content' => 'Mostrar la fecha de la venta en el título.', 'module' => 'Promotor', 'functionality' => 'Ventas', 'assigned' => 'Ricardo'],
            ['content' => 'Agregar mensaje de confirmación de venta.', 'module' => 'Promotor', 'functionality' => 'Ventas', 'assigned' => 'Ricardo'],
            ['content' => 'Implementar lógica backend para pagos completos y diferidos.', 'module' => 'Promotor', 'functionality' => 'Pagos múltiples', 'assigned' => 'Adair'],
            ['content' => 'Diseñar inputs dinámicos para múltiples pagos.', 'module' => 'Promotor', 'functionality' => 'Pagos múltiples', 'assigned' => 'Adair'],
            ['content' => 'Aplicar colores de feedback (éxito, error, advertencia).', 'module' => 'Promotor', 'functionality' => 'Pagos múltiples', 'assigned' => 'Adair'],
            ['content' => 'Revisar y estandarizar que todos los nombres estén en español en la UI.', 'module' => 'Promotor', 'functionality' => 'General', 'assigned' => 'Adair'],
            // Módulo Supervisor
            ['content' => 'Hacer botones funcionales.', 'module' => 'Supervisor', 'functionality' => 'Objetivo', 'assigned' => 'Ricardo'],
            ['content' => 'Calcular % de avance y monto total por promotor.', 'module' => 'Supervisor', 'functionality' => 'Cartera', 'assigned' => 'Adair'],
            ['content' => 'Implementar barra visual de progreso por promotor.', 'module' => 'Supervisor', 'functionality' => 'Cartera', 'assigned' => 'Adair'],
            ['content' => 'Definir lógica de horarios funcionales y BDD.', 'module' => 'Supervisor', 'functionality' => 'Venta', 'assigned' => 'Adair'],
            ['content' => 'Diseñar UI para captura de horarios.', 'module' => 'Supervisor', 'functionality' => 'Venta', 'assigned' => 'Adair'],
            ['content' => 'Mover formulario actual de Prospectados a Supervisar.', 'module' => 'Supervisor', 'functionality' => 'Prospectados', 'assigned' => 'Ricardo'],
            ['content' => 'Implementar botones de actualizar y regresar.', 'module' => 'Supervisor', 'functionality' => 'Prospectados', 'assigned' => 'Ricardo'],
            ['content' => 'Mostrar info capturada por promotora en check del supervisor.', 'module' => 'Supervisor', 'functionality' => 'Prospectados', 'assigned' => 'Ricardo'],
            ['content' => 'Lógica backend de búsqueda (nombre, dirección, teléfono).', 'module' => 'Supervisor', 'functionality' => 'Búsqueda', 'assigned' => 'Adair'],
            ['content' => 'Implementar búsquedas permisivas.', 'module' => 'Supervisor', 'functionality' => 'Búsqueda', 'assigned' => 'Adair'],
            ['content' => 'Validar visibilidad de datos según rol.', 'module' => 'Supervisor', 'functionality' => 'Búsqueda', 'assigned' => 'Adair'],
            ['content' => 'Maquetar formulario y tabla de resultados de búsqueda.', 'module' => 'Supervisor', 'functionality' => 'Búsqueda', 'assigned' => 'Adair'],
            ['content' => 'Incluir apertura también para ejecutivo/supuesto.', 'module' => 'Supervisor', 'functionality' => 'Apertura', 'assigned' => 'Ricardo'],
            // Módulo Ejecutivo
            ['content' => 'Implementar botón “V” que redirija a Desembolso.', 'module' => 'Ejecutivo', 'functionality' => 'Supervisor.Venta', 'assigned' => 'Ricardo'],
            ['content' => 'Maquetar formato grupal de clientes para desembolso.', 'module' => 'Ejecutivo', 'functionality' => 'Supervisor.Venta', 'assigned' => 'Ricardo'],
            // Módulo Desembolso
            ['content' => 'Programar selección de clientes, fecha y registro de falla.', 'module' => 'Desembolso', 'functionality' => 'Falla', 'assigned' => 'Ricardo'],
            ['content' => 'Precargar datos desde sistema.', 'module' => 'Desembolso', 'functionality' => 'Préstamo', 'assigned' => 'Ricardo'],
            ['content' => 'Agregar botones de confirmación y cancelación.', 'module' => 'Desembolso', 'functionality' => 'Préstamo', 'assigned' => 'Ricardo'],
            ['content' => 'Diseñar UI tentativa (pendiente definición funcional).', 'module' => 'Desembolso', 'functionality' => 'Cobranza Semanal', 'assigned' => 'Adair'],
            ['content' => 'Implementar lógica backend de Adelantos/Recuperación.', 'module' => 'Desembolso', 'functionality' => 'Adelantos/Recuperación', 'assigned' => 'Ricardo'],
            ['content' => 'Maquetar interfaz para selección y registro.', 'module' => 'Desembolso', 'functionality' => 'Adelantos/Recuperación', 'assigned' => 'Ricardo'],
            ['content' => 'Programar llenado automático de Desembolso Real.', 'module' => 'Desembolso', 'functionality' => 'Automáticos', 'assigned' => 'Ricardo'],
            ['content' => 'Programar llenado automático de Recréditos.', 'module' => 'Desembolso', 'functionality' => 'Automáticos', 'assigned' => 'Ricardo'],
            ['content' => 'Implementar canvas JS para captura de firmas.', 'module' => 'Desembolso', 'functionality' => 'Firmas', 'assigned' => 'Adair'],
            ['content' => 'Implementar firma electrónica con validación de contraseña.', 'module' => 'Desembolso', 'functionality' => 'Firmas', 'assigned' => 'Ricardo'],
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