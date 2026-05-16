<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'phone',
        'message',
        'provider',
        'status',
        'message_id',
        'provider_response',
        'error_message',
        'retry_count',
        'sent_at',
        'delivered_at',
        'cost',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'retry_count' => 'integer',
        'cost' => 'decimal:4',
    ];

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if SMS was sent successfully.
     */
    public function isSent(): bool
    {
        return in_array($this->status, ['sent', 'delivered']);
    }

    /**
     * Check if SMS failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Scope for sent SMS.
     */
    public function scopeSent($query)
    {
        return $query->whereIn('status', ['sent', 'delivered']);
    }

    /**
     * Scope for failed SMS.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending SMS.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope by provider.
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }
}
