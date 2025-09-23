{{-- resources/views/components/task-card.blade.php --}}
<div class="task-card bg-white p-4 rounded-lg shadow-md border-l-4 {{ $task->assigned === 'Ricardo' ? 'border-blue-500' : 'border-green-500' }}"
     draggable="true"
     id="{{ $task->id }}">
    <div class="font-bold text-gray-800">{{ $task->functionality }}</div>
    <p class="text-sm text-gray-600 mt-1 mb-3">{{ $task->content }}</p>
    <div class="flex justify-between items-center text-xs">
        <span class="font-semibold px-2 py-1 bg-red-100 text-red-700 rounded-full">{{ $task->module }}</span>
        <span class="font-medium text-gray-500">{{ $task->assigned }}</span>
    </div>
</div>