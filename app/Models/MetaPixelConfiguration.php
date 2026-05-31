<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaPixelConfiguration extends Model
{
    protected $fillable = [
        'pixel_id',
        'access_token',
        'is_active',
        'enable_pixel',
        'enable_conversion_api',
        'events_to_track',
        'settings',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'enable_pixel'           => 'boolean',
        'enable_conversion_api'  => 'boolean',
        'events_to_track'        => 'array',
        'settings'               => 'array',
    ];

    protected $hidden = [
        'access_token',
    ];

    /**
     * Get the pixel events for this configuration.
     */
    public function events(): HasMany
    {
        return $this->hasMany(MetaPixelEvent::class, 'pixel_id', 'pixel_id');
    }

    /**
     * Get the active configuration (singleton pattern).
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
