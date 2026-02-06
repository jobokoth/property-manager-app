<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaretakerTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'caretaker_user_id',
        'assigned_by_user_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'caretaker_user_id' => 'integer',
        'assigned_by_user_id' => 'integer',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * Relationship to property
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relationship to caretaker user
     */
    public function caretaker()
    {
        return $this->belongsTo(User::class, 'caretaker_user_id');
    }

    /**
     * Relationship to user who assigned the task
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    /**
     * Mark the task as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Scope for pending tasks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in progress tasks
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed tasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
