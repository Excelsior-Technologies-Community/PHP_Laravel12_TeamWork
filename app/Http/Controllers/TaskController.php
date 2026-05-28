<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $teamId = Auth::user()->current_team_id;
        $tasks = Task::where('team_id', $teamId)->get();
        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:todo,in_progress,done'
        ]);

        Task::create([
            'team_id' => Auth::user()->current_team_id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return back();
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate(['status' => 'required|in:todo,in_progress,done']);
        $task->update(['status' => $request->status]);
        return back();
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return back();
    }
}