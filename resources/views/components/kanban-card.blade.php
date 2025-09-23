<div class="task-card bg-white p-4 rounded-lg shadow-md border-l-4 {{ $task->assigned === 'Ricardo' ? 'border-blue-500' : 'border-green-500' }}"
     draggable="true"
     data-task-id="{{ $task->id }}">
    <div class="font-bold text-gray-800">{{ $task->functionality }}</div>
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