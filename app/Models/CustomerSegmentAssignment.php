<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSegmentAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_segment_id',
        'assigned_at',
        'assigned_by',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the customer.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the segment.
     */
    public function segment()
    {
        return $this->belongsTo(CustomerSegment::class, 'customer_segment_id');
    }
}
