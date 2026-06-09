<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'requires_scheduling',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requires_scheduling' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public const TYPES = [
        'home_service'   => 'Home Service',
        'office_booking' => 'Booking for Office',
        'home_delivery'  => 'Home Delivery',
        'outlet_pickup'  => 'Outlet Pickup',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Service $service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_services')
            ->withPivot('price', 'is_required', 'is_active', 'conditions')
            ->withTimestamps();
    }

    public function orderServices()
    {
        return $this->hasMany(OrderServiceItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeDisplayAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function requiresScheduling(): bool
    {
        return $this->requires_scheduling;
    }
}
