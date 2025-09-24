<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tablero Kanban') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" x-data="{ open: false }">
                    
                    <header class="mb-8 text-center">
                        <h1 class="text-4xl font-bold text-gray-900">Tablero de Módulos por Rol</h1>
                        <p class="text-lg text-gray-600 mt-2">Seguimiento de Tareas</p>
                    </header>

                    <!-- Button to open modal -->
                    <div class="flex justify-end mb-4">
                        <button @click="open = true" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Crear Tarea
                        </button>
                    </div>

                    <!-- Modal -->
                    <div @keydown.escape.window="open = false" x-show="open" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form action="{{ route('kanban.store') }}" method="POST">
                                    @csrf
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                    Crear Nueva Tarea
                                                </h3>
                                                <div class="mt-2">
                                                    <div class="mb-4">
                                                        <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Contenido:</label>
                                                        <textarea id="content" name="content" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="module" class="block text-gray-700 text-sm font-bold mb-2">Módulo:</label>
                                                        <input type="text" id="module" name="module" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="functionality" class="block text-gray-700 text-sm font-bold mb-2">Funcionalidad:</label>
                                                        <input type="text" id="functionality" name="functionality" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="assigned" class="block text-gray-700 text-sm font-bold mb-2">Asignado a:</label>
                                                        <input type="text" id="assigned" name="assigned" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            Crear
                                        </button>
                                        <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

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
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
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