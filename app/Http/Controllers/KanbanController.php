<?php

namespace App\Http\Controllers;

use App\Models\Kanban;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    public function index()
    {
        $tasks = Kanban::orderBy('sort_order')->get()->groupBy('status');

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
            'task_id' => 'required|exists:kanban.kanbans,id',
            'status' => 'required|string',
            'order' => 'required|array',
        ]);

        $task = Kanban::find($request->task_id);
        $task->status = $request->status;
        $task->save();

        foreach ($request->order as $index => $taskId) {
            Kanban::where('id', $taskId)->update(['sort_order' => $index]);
        }

        return response()->json(['status' => 'success']);
    }

    public function deleteTask(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:kanban.kanbans,id',
        ]);

        $task = Kanban::find($request->task_id);
        $task->status = 'eliminated';
        $task->save();

        return response()->json(['status' => 'success']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'module' => 'required|string',
            'functionality' => 'required|string',
            'assigned' => 'required|string',
        ]);

        $maxSortOrder = Kanban::where('status', 'todo')->max('sort_order');

        Kanban::create([
            'content' => $request->content,
            'module' => $request->module,
            'functionality' => $request->functionality,
            'assigned' => $request->assigned,
            'status' => 'todo',
            'sort_order' => $maxSortOrder + 1,
        ]);

        return redirect()->route('kanban.index');
    }
}