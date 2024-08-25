<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_leader_id' => 'required|exists:users,id',
            'team_members' => 'array',
            'team_members.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Return validation errors as a JSON response
        }

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'team_leader_id' => $request->team_leader_id,
            'team_members' => $request->team_members,
        ]);

        return response()->json($project, 201);
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'team_leader_id' => 'sometimes|required|exists:users,id',
            'team_members' => 'array',
            'team_members.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Return validation errors as a JSON response
        }

        $project = Project::findOrFail($id);

        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'team_leader_id' => $request->team_leader_id,
            'team_members' => $request->team_members,
        ]);

        return response()->json($project);
    }
    public function show($id)
    {
        $project = Project::findOrFail($id);
        return response()->json($project);
    }
    public function detail($id)
    {
        $project = Project::findOrFail($id);
        return response()->json($project);
    }
    public function projectAddShow()
    {
        $tls = User::where('role', 'team_leader')->orderBy('id','desc')->get();
        return view('projects.create',['teamLeaders' => $tls]);
    }
    public function index()
    {
        $projects = Project::orderBy('id','desc')->get();
        return response()->json($projects);
    }
    public function projectShow()
    {
        $projects = Project::orderBy('id','desc')->get();
        return view('projects.index',['projects'=>$projects]);
    }
}
