<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'team_leader_id', 'team_members'];

    // Accessor to get team members as an array
    public function getTeamMembersAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    // Mutator to set team members as a comma-separated string
    public function setTeamMembersAttribute($value)
    {
        $this->attributes['team_members'] = is_array($value) ? implode(',', $value) : $value;
    }

    public function teamLeader()
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    public function getTeamMembers()
    {
        return User::whereIn('id', $this->team_members)->get();
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
