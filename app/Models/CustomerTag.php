<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to auto-generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get customers with this tag.
     */
    public function customers()
    {
        return $this->belongsToMany(User::class, 'customer_tag_assignments', 'customer_tag_id', 'user_id')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }

    /**
     * Get tag assignments.
     */
    public function assignments()
    {
        return $this->hasMany(CustomerTagAssignment::class);
    }

    /**
     * Scope for active tags.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get customer count with this tag.
     */
    public function getCustomerCountAttribute(): int
    {
        return $this->customers()->count();
    }
}
