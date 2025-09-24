<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tablero Kanban') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <header class="mb-8 text-center">
                        <h1 class=º"text-4xl font-bold text-gray-900">Tablero de Módulos por Rol</h1>
                        <p class="text-lg text-gray-600 mt-2">Seguimiento de Tareas</p>
                    </header>

                    <!-- Contenedor del Tablero Kanban -->
                    <div id="kanban-board" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        
                        <!-- Columna: Por Hacer -->
                        <div class="kanban-column bg-gray-200 rounded-xl shadow-inner" data-column-id="todo">
                            <h2 class="text-xl font-semibold p-4 text-gray-700 border-b-2 border-gray-300">Por Hacer</h2>
                            <div class="p-4 space-y-4 task-list min-h-[60vh]">
                                @foreach ($tasks['todo'] as $task)
                                    @include('components.kanban-card', ['task' => $task])
                                @endforeach
                            </div>
                        </div>

                        <!-- Columna: En Progreso (Ricardo) -->
                        <div class="kanban-column bg-blue-100 rounded-xl shadow-inner" data-column-id="in-progress-ricardo">
                            <h2 class="text-xl font-semibold p-4 text-blue-800 border-b-2 border-blue-200">En Progreso (Ricardo)</h2>
                            <div class="p-4 space-y-4 task-list min-h-[60vh]">
                                @foreach ($tasks['in-progress-ricardo'] as $task)
                                    @include('components.kanban-card', ['task' => $task])
                                @endforeach
                            </div>
                        </div>

                        <!-- Columna: En Progreso (Adair) -->
                        <div class="kanban-column bg-green-100 rounded-xl shadow-inner" data-column-id="in-progress-adair">
                            <h2 class="text-xl font-semibold p-4 text-green-800 border-b-2 border-green-200">En Progreso (Adair)</h2>
                            <div class="p-4 space-y-4 task-list min-h-[60vh]">
                               @foreach ($tasks['in-progress-adair'] as $task)
                                    @include('components.kanban-card', ['task' => $task])
                                @endforeach
                            </div>
                        </div>

                        <!-- Columna: Terminado -->
                        <div class="kanban-column bg-purple-100 rounded-xl shadow-inner" data-column-id="done">
                            <h2 class="text-xl font-semibold p-4 text-purple-800 border-b-2 border-purple-200">Terminado</h2>
                            <div class="p-4 space-y-4 task-list min-h-[60vh]">
                                @foreach ($tasks['done'] as $task)
                                    @include('components.kanban-card', ['task' => $task])
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setupDragAndDrop();
        });

        function setupDragAndDrop() {
            const cards = document.querySelectorAll('.task-card');
            const columns = document.querySelectorAll('.kanban-column .task-list');
            let draggingCard = null;

            cards.forEach(card => {
                card.addEventListener('dragstart', (e) => {
                    card.classList.add('dragging');
                    draggingCard = card;
                });

                card.addEventListener('dragend', () => {
                    card.classList.remove('dragging');
                    
                    const newColumnElement = draggingCard.closest('.kanban-column');
                    const newStatus = newColumnElement.dataset.columnId;
                    const taskIdsInOrder = [...newColumnElement.querySelectorAll('.task-card')].map(c => c.dataset.taskId);
                    
                    updateTaskOnServer(draggingCard.dataset.taskId, newStatus, taskIdsInOrder);
                    draggingCard = null;
                });
            });

            columns.forEach(column => {
                column.addEventListener('dragover', e => {
                    e.preventDefault();
                    const afterElement = getDragAfterElement(column, e.clientY);
                    if (draggingCard) {
                        if (afterElement == null) {
                            column.appendChild(draggingCard);
                        } else {
                            column.insertBefore(draggingCard, afterElement);
                        }
                    }
                });
            });
        }
        
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.task-card:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        async function updateTaskOnServer(taskId, status, order) {
            try {
                const response = await fetch('{{ route("kanban.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        task_id: taskId,
                        status: status,
                        order: order
                    })
                });

                if (!response.ok) console.error('Error al actualizar la tarea.');
                
                const result = await response.json();
                console.log('Tarea actualizada:', result);

            } catch (error) {
                console.error('Error de red:', error);
            }
        }
        
        async function deleteTask(taskId) {
            try {
                const response = await fetch('{{ route("kanban.delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        task_id: taskId
                    })
                });

                if (response.ok) {
                    document.querySelector(`[data-task-id="${taskId}"]`).remove();
                } else {
                    console.error('Error al eliminar la tarea.');
                }
            } catch (error) {
                console.error('Error de red:', error);
            }
        }
    </script>
    @endpush
</x-app-layout>
