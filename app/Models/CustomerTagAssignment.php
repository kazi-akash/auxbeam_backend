<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTagAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_tag_id',
        'assigned_at',
        'assigned_by',
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
     * Get the tag.
     */
    public function tag()
    {
        return $this->belongsTo(CustomerTag::class, 'customer_tag_id');
    }

    /**
     * Get the user who assigned the tag.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
