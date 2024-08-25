<?php

namespace App\Http\Controllers\Project;

use App\Events\TaskAssigned;
use App\Events\TaskUpdated;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
    {
        return Task::with('subtasks')->get();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id', // Validate project_id
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in-progress,completed',
            'assigned_to' => 'nullable|exists:users,id',
            'assigned_by' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Return validation errors as a JSON response
        }

        $task = Task::create($request->all());

        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        return $task->load('subtasks');
    }

    public function update(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in-progress,completed',
            'assigned_to' => 'nullable|exists:users,id',
            'assigned_by' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Return validation errors as a JSON response
        }

        $task->update($request->all());

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(null, 204);
    }
    public function getTasksByProject(Project $project)
    {
//        return $project->tasks()->with('subtasks')->get();
        return $project->with('tasks.subtasks')->get();
    }
    public function updateTaskStatus(Request $request, Task $task)
    {
//        $task->status = $request->input('status');
//        $task->completed_at = $request->input('status') === 'completed' ? now() : null;
        $task->status = 'completed';
        $task->completed_at = now();
        $task->save();

        $assignedBy = User::find($task->assigned_to);
        $assignedTo = User::find($task->assigned_by);
        event(new TaskAssigned($task));

//        event(new TaskUpdated('completed task: '.$task->title));
        // Notify the assigned user and the team leader
        $assignedBy->notify(new TaskUpdatedNotification($task));
        $assignedTo->notify(new TaskUpdatedNotification($task));

        return response()->json(['message' => 'Task status updated successfully'], 200);
    }
}
