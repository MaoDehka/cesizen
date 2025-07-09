<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'gestsup_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'assignee_id',
        'creator_id',
        'estimated_hours',
        'spent_hours',
        'due_date',
        'branch_name',
        'pull_request_url',
        'deployed_at'
    ];
    
    protected $casts = [
        'due_date' => 'datetime',
        'deployed_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'spent_hours' => 'decimal:2'
    ];
    
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}