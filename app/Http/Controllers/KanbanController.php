<?php

namespace App\Http\Controllers;

use App\Models\Kanban;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    public function index()
    {
        $tasks = Task::orderBy('order')->get()->groupBy('status');

        $groupedTasks = [
            'todo' => $tasks->get('todo', []),
            'in-progress-ricardo' => $tasks->get('in-progress-ricardo', []),
            'in-progress-adair' => $tasks->get('in-progress-adair', []),
            'done' => $tasks->get('done', []),
        ];

        return view('kanban.index', ['tasks' => $groupedTasks]);
    }

    public function updateTaskStatus(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'status' => 'required|string',
            'order' => 'required|array',
        ]);

        $task = Task::find($request->task_id);
        $task->status = $request->status;
        $task->save();

        foreach ($request->order as $index => $taskId) {
            Task::where('id', $taskId)->update(['order' => $index]);
        }

        return response()->json(['status' => 'success']);
    }
}