<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderServiceItem extends Model
{
    use HasFactory;

    protected $table = 'order_services';

    protected $fillable = [
        'order_id',
        'service_id',
        'service_name',
        'service_type',
        'price',
        'scheduled_date',
        'scheduled_time',
        'scheduling_notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'scheduled_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getTypeDisplayAttribute(): string
    {
        return Service::TYPES[$this->service_type] ?? $this->service_type;
    }

    public function isScheduled(): bool
    {
        return !is_null($this->scheduled_date);
    }
}
