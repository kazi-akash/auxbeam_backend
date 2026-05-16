<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'remind_at',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the order that owns the reminder.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who created the reminder.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user assigned to the reminder.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope for pending reminders.
     */
    public function scopePending($query)
    {
        return $query->where('is_completed', false)
                    ->where('remind_at', '<=', now());
    }

    /**
     * Scope for upcoming reminders.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('is_completed', false)
                    ->where('remind_at', '>', now());
    }

    /**
     * Mark reminder as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }
}
