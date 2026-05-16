<?php

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Order Confirmation',
                'slug' => 'order-confirmation',
                'type' => 'order_confirmation',
                'content' => 'Dear {customer_name}, your order #{order_number} has been placed successfully. Total: BDT {total_amount}. Thank you for shopping with us!',
                'variables' => ['customer_name', 'order_number', 'total_amount', 'order_date'],
                'is_active' => true,
                'description' => 'Sent when a new order is placed',
            ],
            [
                'name' => 'Order Status Update',
                'slug' => 'order-status-update',
                'type' => 'order_status',
                'content' => 'Dear {customer_name}, your order #{order_number} is now {status}. Tracking: {tracking_number}. Thank you!',
                'variables' => ['customer_name', 'order_number', 'status', 'tracking_number'],
                'is_active' => true,
                'description' => 'Sent when order status changes',
            ],
            [
                'name' => 'OTP Verification',
                'slug' => 'otp-verification',
                'type' => 'otp',
                'content' => 'Your OTP code is: {otp}. Valid for {validity}. Do not share this code with anyone.',
                'variables' => ['otp', 'validity'],
                'is_active' => true,
                'description' => 'Sent for OTP verification',
            ],
            [
                'name' => 'Delivery Notification',
                'slug' => 'delivery-notification',
                'type' => 'delivery',
                'content' => 'Dear {customer_name}, your order #{order_number} is out for delivery. Tracking: {tracking_number}. Please keep your phone available.',
                'variables' => ['customer_name', 'order_number', 'tracking_number'],
                'is_active' => true,
                'description' => 'Sent when order is out for delivery',
            ],
            [
                'name' => 'Order Shipped',
                'slug' => 'order-shipped',
                'type' => 'order_status',
                'content' => 'Dear {customer_name}, your order #{order_number} has been shipped. Tracking: {tracking_number}. Expected delivery: {delivery_date}.',
                'variables' => ['customer_name', 'order_number', 'tracking_number', 'delivery_date'],
                'is_active' => true,
                'description' => 'Sent when order is shipped',
            ],
            [
                'name' => 'Order Delivered',
                'slug' => 'order-delivered',
                'type' => 'order_status',
                'content' => 'Dear {customer_name}, your order #{order_number} has been delivered. Thank you for shopping with us! Please rate your experience.',
                'variables' => ['customer_name', 'order_number'],
                'is_active' => true,
                'description' => 'Sent when order is delivered',
            ],
            [
                'name' => 'Order Cancelled',
                'slug' => 'order-cancelled',
                'type' => 'order_status',
                'content' => 'Dear {customer_name}, your order #{order_number} has been cancelled. Reason: {reason}. If you have any questions, please contact us.',
                'variables' => ['customer_name', 'order_number', 'reason'],
                'is_active' => true,
                'description' => 'Sent when order is cancelled',
            ],
            [
                'name' => 'Payment Reminder',
                'slug' => 'payment-reminder',
                'type' => 'payment',
                'content' => 'Dear {customer_name}, payment for order #{order_number} (BDT {amount}) is pending. Please complete payment to process your order.',
                'variables' => ['customer_name', 'order_number', 'amount'],
                'is_active' => true,
                'description' => 'Sent as payment reminder',
            ],
            [
                'name' => 'Welcome Message',
                'slug' => 'welcome-message',
                'type' => 'welcome',
                'content' => 'Welcome to our store, {customer_name}! Thank you for registering. Enjoy shopping with exclusive deals and offers!',
                'variables' => ['customer_name'],
                'is_active' => true,
                'description' => 'Sent to new customers',
            ],
            [
                'name' => 'Flash Deal Alert',
                'slug' => 'flash-deal-alert',
                'type' => 'marketing',
                'content' => 'Flash Deal Alert! {deal_name} - Up to {discount}% OFF! Valid till {end_date}. Shop now: {link}',
                'variables' => ['deal_name', 'discount', 'end_date', 'link'],
                'is_active' => true,
                'description' => 'Sent for flash deal notifications',
            ],
        ];

        foreach ($templates as $template) {
            SmsTemplate::create($template);
        }

        $this->command->info('✅ SMS templates seeded successfully!');
        $this->command->info('   - 10 SMS Templates created');
    }
}
