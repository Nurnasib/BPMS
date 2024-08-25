<table>
    <thead>
    <tr>
        <th>Project ID</th>
        <th>Project Name</th>
        <th>Team Leader</th>
        <th>Total Tasks</th>
        <th>Total Subtasks</th>
        <th>Completed Tasks</th>
        <th>Overdue Tasks</th>
        <th>Task Completion %</th>
        <th>Average Completion Time (hrs)</th>
        <th>Team Member</th>
        <th>Total Subtasks</th>
        <th>Completed Subtasks</th>
        <th>Overdue Subtasks</th>
        <th>Completion %</th>
    </tr>
    </thead>
    <tbody>
    @foreach($report as $project)
        <tr>
            <td>{{ $project['project_id'] }}</td>
            <td>{{ $project['project_name'] }}</td>
            <td>{{ $project['team_leader'] }}</td>
            <td>{{ $project['total_tasks'] }}</td>
            <td>{{ $project['total_subtasks'] }}</td>
            <td>{{ $project['completed_tasks'] }}</td>
            <td>{{ $project['overdue_tasks'] }}</td>
            <td>{{ $project['task_completion_percentage'] }}</td>
            <td>{{ $project['average_completion_time'] }}</td>
            <td colspan="5"></td>
        </tr>
        @foreach($project['team_members'] as $member)
            <tr>
                <td colspan="9"></td>
                <td>{{ $member['team_member_name'] }}</td>
                <td>{{ $member['total_subtasks'] }}</td>
                <td>{{ $member['completed_subtasks'] }}</td>
                <td>{{ $member['overdue_subtasks'] }}</td>
                <td>{{ $member['completion_percentage'] }}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
