<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'title', 'description', 'status', 'assigned_to', 'assigned_by', 'due_date', 'completed_at',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }
    public function assignedTo()
    {
        return $this->belongsTo(User::class);
    }
    public function assignedBy()
    {
        return $this->belongsTo(User::class);
    }
}
