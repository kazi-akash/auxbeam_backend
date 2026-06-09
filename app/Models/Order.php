<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'order_type',
        'order_source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'referrer_url',
        'shipping_address_id',
        'billing_address_id',
        'subtotal',
        'shipping_cost',
        'service_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'coupon_id',
        'shipping_method',
        'delivery_type',
        'scheduled_date',
        'scheduled_time',
        'tracking_number',
        'status',
        'payment_status',
        'notes',
        'follow_up_at',
        'follow_up_completed',
        'customer_name',
        'customer_email',
        'customer_phone',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'service_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_preorder' => 'boolean',
        'preorder_deposit_paid' => 'decimal:2',
        'preorder_remaining_amount' => 'decimal:2',
        'follow_up_at' => 'datetime',
        'follow_up_completed' => 'boolean',
        'scheduled_date' => 'date',
    ];

    public const DELIVERY_TYPES = [
        'home_service'   => 'Home Service',
        'office_booking' => 'Booking for Office',
        'home_delivery'  => 'Home Delivery',
        'outlet_pickup'  => 'Outlet Pickup',
    ];

    // Payment methods that are valid per delivery type
    public const PAYMENT_METHODS_BY_DELIVERY = [
        'home_service'   => ['ssl_commerz', 'bkash', 'nagad', 'cash_on_service', 'pos_on_service'],
        'office_booking' => ['ssl_commerz', 'bkash', 'nagad', 'cash_on_delivery', 'pos_on_delivery'],
        'home_delivery'  => ['ssl_commerz', 'bkash', 'nagad', 'cash_on_delivery', 'pos_on_delivery'],
        'outlet_pickup'  => ['ssl_commerz', 'bkash', 'nagad', 'cash_on_delivery', 'pos_on_delivery'],
    ];

    /**
     * Boot method to auto-generate order number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'SS';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $number = $prefix . $date . $random;

        while (self::where('order_number', $number)->exists()) {
            $random = strtoupper(Str::random(4));
            $number = $prefix . $date . $random;
        }

        return $number;
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get shipping address.
     */
    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    /**
     * Get billing address.
     */
    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    /**
     * Get order items.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get services attached to this order.
     */
    public function orderServices()
    {
        return $this->hasMany(OrderServiceItem::class);
    }

    /**
     * Get coupon.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get payment.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get payments.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get invoice.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get returns.
     */
    public function returns()
    {
        return $this->hasMany(ProductReturn::class);
    }

    /**
     * Get refunds.
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Get reviews.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get order notes.
     */
    public function orderNotes()
    {
        return $this->hasMany(OrderNote::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get order reminders.
     */
    public function reminders()
    {
        return $this->hasMany(OrderReminder::class)->orderBy('remind_at', 'asc');
    }

    /**
     * Get status history.
     */
    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Add a note to the order.
     */
    public function addNote(string $note, string $noteType = 'internal', ?int $userId = null, bool $notifyCustomer = false): OrderNote
    {
        return $this->orderNotes()->create([
            'user_id' => $userId,
            'note' => $note,
            'note_type' => $noteType,
            'is_customer_notified' => $notifyCustomer,
        ]);
    }

    /**
     * Add a reminder to the order.
     */
    public function addReminder(string $title, \DateTime $remindAt, ?string $description = null, ?int $createdBy = null, ?int $assignedTo = null): OrderReminder
    {
        return $this->reminders()->create([
            'title' => $title,
            'description' => $description,
            'remind_at' => $remindAt,
            'created_by' => $createdBy,
            'assigned_to' => $assignedTo,
        ]);
    }

    /**
     * Log status change.
     */
    public function logStatusChange(string $toStatus, ?string $fromStatus = null, ?int $userId = null, ?string $note = null): OrderStatusHistory
    {
        return $this->statusHistory()->create([
            'from_status' => $fromStatus ?? $this->status,
            'to_status' => $toStatus,
            'user_id' => $userId,
            'note' => $note,
        ]);
    }

    /**
     * Update order status with logging.
     */
    public function updateStatus(string $newStatus, ?int $userId = null, ?string $note = null): bool
    {
        $oldStatus = $this->status;
        
        $this->status = $newStatus;
        $saved = $this->save();

        if ($saved) {
            $this->logStatusChange($newStatus, $oldStatus, $userId, $note);
        }

        return $saved;
    }

    /**
     * Check if POS order.
     */
    public function isPosOrder(): bool
    {
        return $this->order_type === 'in_store';
    }

    /**
     * Check if online order.
     */
    public function isOnlineOrder(): bool
    {
        return $this->order_type === 'online';
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing']);
    }

    /**
     * Calculate totals.
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total_price');
        $this->service_amount = $this->orderServices->sum('price');
        $this->total_amount = $this->subtotal + $this->shipping_cost + $this->service_amount + $this->tax_amount - $this->discount_amount;
    }

    /**
     * Check if delivery type requires home visit.
     */
    public function isHomeService(): bool
    {
        return $this->delivery_type === 'home_service';
    }

    /**
     * Get delivery type display name.
     */
    public function getDeliveryTypeDisplayAttribute(): string
    {
        return self::DELIVERY_TYPES[$this->delivery_type] ?? $this->delivery_type ?? 'Standard';
    }

    /**
     * Get allowed payment methods for this order's delivery type.
     */
    public function getAllowedPaymentMethods(): array
    {
        return self::PAYMENT_METHODS_BY_DELIVERY[$this->delivery_type] ?? [
            'ssl_commerz', 'bkash', 'nagad', 'cash_on_delivery', 'pos_on_delivery',
        ];
    }

    /**
     * Get customer name (from user or POS data).
     */
    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->customer_name) {
            return $this->customer_name;
        }

        return $this->user ? $this->user->full_name : 'Guest';
    }

    /**
     * Get customer email (from user or POS data).
     */
    public function getCustomerDisplayEmailAttribute(): ?string
    {
        return $this->customer_email ?? $this->user?->email;
    }

    /**
     * Scope for POS orders.
     */
    public function scopePosOrders($query)
    {
        return $query->where('order_type', 'in_store');
    }

    /**
     * Scope for online orders.
     */
    public function scopeOnlineOrders($query)
    {
        return $query->where('order_type', 'online');
    }

    /**
     * Scope for pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Check if order is preorder.
     */
    public function isPreorder(): bool
    {
        return $this->is_preorder === true;
    }

    /**
     * Check if preorder deposit is paid.
     */
    public function isPreorderDepositPaid(): bool
    {
        return $this->is_preorder && $this->preorder_payment_status === 'deposit_paid';
    }

    /**
     * Check if preorder is fully paid.
     */
    public function isPreorderFullyPaid(): bool
    {
        return $this->is_preorder && $this->preorder_payment_status === 'fully_paid';
    }

    /**
     * Get remaining preorder amount.
     */
    public function getRemainingPreorderAmount(): float
    {
        if (!$this->is_preorder) {
            return 0;
        }

        return $this->preorder_remaining_amount ?? 0;
    }

    /**
     * Scope for preorder orders.
     */
    public function scopePreorders($query)
    {
        return $query->where('is_preorder', true);
    }

    /**
     * Scope for preorders with pending balance.
     */
    public function scopePreordersPendingBalance($query)
    {
        return $query->where('is_preorder', true)
            ->where('preorder_payment_status', 'deposit_paid');
    }

    /**
     * Scope for incomplete orders.
     */
    public function scopeIncomplete($query)
    {
        return $query->where('status', 'incomplete');
    }

    /**
     * Scope for orders by source.
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('order_source', $source);
    }

    /**
     * Scope for orders with UTM campaign.
     */
    public function scopeWithUtmCampaign($query, string $campaign)
    {
        return $query->where('utm_campaign', $campaign);
    }

    /**
     * Scope for orders needing follow-up.
     */
    public function scopeNeedingFollowUp($query)
    {
        return $query->where('follow_up_completed', false)
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<=', now());
    }

    /**
     * Get order source display name.
     */
    public function getOrderSourceDisplayAttribute(): string
    {
        return match($this->order_source) {
            'website' => 'Website',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'whatsapp' => 'WhatsApp',
            'phone_call' => 'Phone Call',
            'manual' => 'Manual Entry',
            default => 'Unknown',
        };
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'incomplete' => 'Incomplete',
            'good_but_no_response' => 'Good but No Response',
            'advance_payment' => 'Advance Payment',
            'on_hold' => 'On Hold',
            'ready_to_ship' => 'Ready to Ship',
            'shipped' => 'Shipped',
            'complete' => 'Complete',
            'cancelled' => 'Cancelled',
            'return_requested' => 'Return Requested',
            'return_approved' => 'Return Approved',
            'refunded' => 'Refunded',
            default => 'Unknown',
        };
    }
}
