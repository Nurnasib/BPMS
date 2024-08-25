<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ProjectsReportExport implements FromView
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        $projects = Project::with(['tasks.subtasks', 'teamLeader', 'tasks.assignedTo', 'tasks.subtasks.assignedTo'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();

        $report = $projects->map(function ($project) {
            $tasks = $project->tasks;
            $totalSubtasks = $tasks->flatMap->subtasks->count();
            $taskCompletion = $tasks->flatMap->subtasks->pluck('status')->map(function ($status) {
                return $status === 'completed' ? 1 : 0;
            })->sum();

            $completedTasks = $tasks->filter(fn ($task) => $task->status === 'completed')->count();
            $overdueTasks = $tasks->filter(fn ($task) => $task->due_date && $task->due_date < now() && $task->status !== 'completed')->count();

            $averageCompletionTimeMinutes = $tasks->flatMap->subtasks->filter(fn ($subtask) => $subtask->completed_at)->map(function ($subtask) {
                $completedAt = Carbon::parse($subtask->completed_at);
                return $completedAt->diffInMinutes($subtask->created_at);
            })->average();
            $averageCompletionTime = $averageCompletionTimeMinutes ? $averageCompletionTimeMinutes / 60 : 0;

            // Calculate team member statistics
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
                        'assigned_to' => $task->assignedTo->name ?? null,
                        'assigned_by' => $task->assignedBy->name ?? null,
                        'due_date' => $task->due_date,
                        'completed_at' => $task->completed_at,
                        'subtasks' => $task->subtasks->map(function ($subtask) {
                            return [
                                'subtask_id' => $subtask->id,
                                'title' => $subtask->title,
                                'status' => $subtask->status,
                                'assigned_to' => $subtask->assignedTo->name ?? null,
                                'assigned_by' => $subtask->assignedBy->name ?? null,
                                'due_date' => $subtask->due_date,
                                'completed_at' => $subtask->completed_at,
                                'created_at' => $subtask->created_at,
                            ];
                        }),
                    ];
                }),
                'task_completion_percentage' => $totalSubtasks > 0 ? ($taskCompletion / $totalSubtasks) * 100 : 0,
                'total_tasks' => $tasks->count(),
                'total_subtasks' => $totalSubtasks,
                'completed_tasks' => $completedTasks,
                'overdue_tasks' => $overdueTasks,
                'average_completion_time' => $averageCompletionTime,
                'team_members' => $teamMembers,
            ];
        });

        return view('exports.projects', [
            'report' => $report
        ]);
    }
//    public function collection()
//    {
//        return Project::all();
//    }
}
