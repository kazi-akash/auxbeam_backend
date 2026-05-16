<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'note',
        'note_type',
        'is_customer_notified',
    ];

    protected $casts = [
        'is_customer_notified' => 'boolean',
    ];

    /**
     * Get the order that owns the note.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who created the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
