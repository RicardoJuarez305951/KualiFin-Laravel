<div class="task-card bg-white p-4 rounded-lg shadow-md border-l-4 {{ $task->assigned === 'Ricardo' ? 'border-blue-500' : 'border-green-500' }}"
     draggable="true"
     data-task-id="{{ $task->id }}">
    <div class="flex justify-between items-center">
        <div class="font-bold text-gray-800">{{ $task->functionality }}</div>
        <button onclick="deleteTask({{ $task->id }})" class="text-gray-500 hover:text-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <p class="text-sm text-gray-600 mt-1 mb-3">{{ $task->content }}</p>
    <div class="flex justify-between items-center text-xs">
        <span class="font-semibold px-2 py-1 bg-gray-100 text-gray-700 rounded-full">{{ $task->module }}</span>
        <span class="font-medium text-gray-500">{{ $task->assigned }}</span>
    </div>
</div>

<style>
    .task-card {
        cursor: grab;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .task-card:active {
        cursor: grabbing;
    }
    .dragging {
        opacity: 0.5;
        transform: rotate(3deg);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>