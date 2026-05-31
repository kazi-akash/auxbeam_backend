<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaPixelEvent extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'event_name',
        'event_id',
        'event_data',
        'source',
        'sent_to_facebook',
        'facebook_response',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'event_data'       => 'array',
        'sent_to_facebook' => 'boolean',
        'sent_at'          => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope: filter by event name.
     */
    public function scopeEventName($query, string $name)
    {
        return $query->where('event_name', $name);
    }

    /**
     * Scope: filter by source (browser / server).
     */
    public function scopeSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope: filter by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
