<?php

namespace App\Http\Controllers;

use App\Exports\ProjectsReportExport;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function getProjectReport(Request $request)
    {
        $this->validate($request, [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $projects = Project::with(['tasks.subtasks', 'teamLeader'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $report = $projects->map(function ($project) {
            $tasks = $project->tasks;
            $taskCompletion = $tasks->flatMap->subtasks->pluck('status')->map(function ($status) {
                return $status === 'completed' ? 1 : 0;
            })->sum();

            $totalTasks = $tasks->count();
            $totalSubtasks = $tasks->flatMap->subtasks->count();
            $completedTasks = $tasks->filter(fn ($task) => $task->status === 'completed')->count();
            $overdueTasks = $tasks->filter(fn ($task) => $task->due_date && $task->due_date < now() && $task->status !== 'completed')->count();

            $averageCompletionTime1 = $tasks->flatMap->subtasks->filter(fn ($subtask) => $subtask->completed_at)->map(function ($subtask) {
                $completedAt = Carbon::parse($subtask->completed_at);
                return $completedAt->diffInMinutes($subtask->created_at);
            })->average();
            $averageCompletionTime = $averageCompletionTime1/60;
            $teamMembers = User::whereIn('id', $project->team_members)->get()->map(function ($member) use ($tasks) {
                $subtasks = $tasks->flatMap->subtasks->where('assigned_to', $member->id);
                $completedSubtasks = $subtasks->where('status', 'completed')->count();
                $overdueSubtasks = $subtasks->filter(fn ($subtask) => $subtask->due_date && $subtask->due_date < now() && $subtask->status !== 'completed')->count();
                $totalSubtasks = $subtasks->count();

                return [
                    'team_member_id' => $member->id,
                    'team_member_name' => $member->name,
                    'total_subtasks' => $totalSubtasks,
                    'completed_subtasks' => $completedSubtasks,
                    'overdue_subtasks' => $overdueSubtasks,
                    'completion_percentage' => $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0,
                ];
            });
            return [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'team_leader' => $project->teamLeader->name,
                'tasks' => $tasks->map(function ($task) {
                    return [
                        'task_id' => $task->id,
                        'title' => $task->title,
                        'status' => $task->status,
                        'assigned_to' => $task->assignedTo->name?? null,
                        'assigned_by' => $task->assignedBy->name?? null,
                        'due_date' => $task->due_date,
                        'completed_at' => $task->completed_at,
                        'subtasks' => $task->subtasks->map(function ($subtask) {
                            return [
                                'subtask_id' => $subtask->id,
                                'title' => $subtask->title,
                                'status' => $subtask->status,
                                'assigned_to' => $subtask->assignedTo->name?? null,
                                'assigned_by' => $subtask->assignedBy->name?? null,
                                'due_date' => $subtask->due_date,
                                'completed_at' => $subtask->completed_at,
                                'created_at' => $subtask->created_at,
                            ];
                        }),
                    ];
                }),
                'task_completion_percentage' => $totalSubtasks > 0 ? ($taskCompletion / $totalSubtasks) * 100 : 0,
                'total_tasks' => $totalTasks,
                'total_subtasks' => $totalSubtasks,
                'completed_tasks' => $completedTasks,
                'overdue_tasks' => $overdueTasks,
                'average_completion_time' => $averageCompletionTime,
                'team_members' => $teamMembers,
            ];
        });

        return response()->json($report);
    }
    public function exportProjectsReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        return Excel::download(new ProjectsReportExport($startDate, $endDate), 'projects_report.xlsx');
    }
}
