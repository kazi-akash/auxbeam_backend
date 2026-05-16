<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_orders',
        'completed_orders',
        'cancelled_orders',
        'returned_orders',
        'total_spent',
        'average_order_value',
        'lifetime_value',
        'cod_orders',
        'cod_completed',
        'cod_cancelled',
        'cod_success_rate',
        'online_payment_orders',
        'online_payment_completed',
        'first_order_at',
        'last_order_at',
        'days_since_last_order',
        'order_frequency_days',
        'risk_score',
        'risk_level',
        'vip_score',
        'is_vip',
        'last_calculated_at',
    ];

    protected $casts = [
        'total_orders' => 'integer',
        'completed_orders' => 'integer',
        'cancelled_orders' => 'integer',
        'returned_orders' => 'integer',
        'total_spent' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'lifetime_value' => 'decimal:2',
        'cod_orders' => 'integer',
        'cod_completed' => 'integer',
        'cod_cancelled' => 'integer',
        'cod_success_rate' => 'decimal:2',
        'online_payment_orders' => 'integer',
        'online_payment_completed' => 'integer',
        'first_order_at' => 'datetime',
        'last_order_at' => 'datetime',
        'days_since_last_order' => 'integer',
        'order_frequency_days' => 'integer',
        'risk_score' => 'decimal:2',
        'vip_score' => 'decimal:2',
        'is_vip' => 'boolean',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * Get the customer.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if customer is high risk.
     */
    public function isHighRisk(): bool
    {
        return $this->risk_level === 'high';
    }

    /**
     * Check if customer is VIP.
     */
    public function isVip(): bool
    {
        return $this->is_vip === true;
    }

    /**
     * Get risk level color.
     */
    public function getRiskLevelColorAttribute(): string
    {
        return match($this->risk_level) {
            'low' => '#10B981',
            'medium' => '#F59E0B',
            'high' => '#EF4444',
            default => '#6B7280',
        };
    }

    /**
     * Scope for VIP customers.
     */
    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    /**
     * Scope for high risk customers.
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'high');
    }

    /**
     * Scope for repeat customers.
     */
    public function scopeRepeat($query)
    {
        return $query->where('completed_orders', '>=', 2);
    }
}
