<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'criteria',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Boot method to auto-generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($segment) {
            if (empty($segment->slug)) {
                $segment->slug = Str::slug($segment->name);
            }
        });
    }

    /**
     * Get customers in this segment.
     */
    public function customers()
    {
        return $this->belongsToMany(User::class, 'customer_segment_assignments', 'customer_segment_id', 'user_id')
            ->withPivot('assigned_at', 'assigned_by', 'notes')
            ->withTimestamps();
    }

    /**
     * Get segment assignments.
     */
    public function assignments()
    {
        return $this->hasMany(CustomerSegmentAssignment::class);
    }

    /**
     * Scope for active segments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered by priority.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('name', 'asc');
    }

    /**
     * Get customer count in this segment.
     */
    public function getCustomerCountAttribute(): int
    {
        return $this->customers()->count();
    }
}
