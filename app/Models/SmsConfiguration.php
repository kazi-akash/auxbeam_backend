<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'is_active',
        'is_default',
        'credentials',
        'settings',
        'priority',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Scope for active configurations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default configuration.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get configuration by provider.
     */
    public static function getByProvider(string $provider): ?self
    {
        return self::where('provider', $provider)->first();
    }

    /**
     * Get default configuration.
     */
    public static function getDefault(): ?self
    {
        return self::default()->active()->first();
    }
}
