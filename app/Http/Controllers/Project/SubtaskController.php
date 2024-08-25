<?php

namespace App\Http\Controllers\Project;

use App\Events\TaskAssigned;
use App\Http\Controllers\Controller;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubtaskController extends Controller
{
    public function index()
    {
        return Subtask::all();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
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

        $subtask = Subtask::create($request->all());

        return response()->json($subtask, 201);
    }

    public function show(Subtask $subtask)
    {
        return $subtask;
    }

    public function update(Request $request, Subtask $subtask)
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

        $subtask->update($request->all());

        return response()->json($subtask);
    }

    public function destroy(Subtask $subtask)
    {
        $subtask->delete();

        return response()->json(null, 204);
    }
    public function updateSubtaskStatus(Request $request, Subtask $subtask)
    {
//        $task->status = $request->input('status');
//        $task->completed_at = $request->input('status') === 'completed' ? now() : null;
        $subtask->status = 'completed';
        $subtask->completed_at = now();
        $subtask->save();

        $assignedBy = User::find($subtask->assigned_to);
        $assignedTo = User::find($subtask->assigned_by);
//        event(new TaskAssigned($subtask));
//
//
        $assignedTo->notify(new TaskUpdatedNotification(['data'=>$subtask, 'assign'=>$subtask->assigned_to]));
        $assignedBy->notify(new TaskUpdatedNotification(['data'=>$subtask, 'assign'=>$subtask->assigned_by]));



        return response()->json(['message' => 'Subtask status updated successfully'], 200);
    }
}
