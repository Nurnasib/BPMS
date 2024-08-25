<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'title', 'description', 'status', 'assigned_to', 'assigned_by', 'due_date', 'completed_at',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
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
