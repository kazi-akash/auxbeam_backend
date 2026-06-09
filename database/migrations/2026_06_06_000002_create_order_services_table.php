<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Services attached to an order (snapshot at time of order)
        Schema::create('order_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('service_name');                 // snapshot
            $table->enum('service_type', [
                'home_service',
                'office_booking',
                'home_delivery',
                'outlet_pickup',
            ]);
            $table->decimal('price', 10, 2)->default(0);   // snapshot of price at time of order
            // For office_booking: scheduling info
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->text('scheduling_notes')->nullable();
            $table->timestamps();
        });

        // Add delivery_type + service_amount to orders table
        Schema::table('orders', function (Blueprint $table) {
            // delivery_type replaces/augments shipping_method for service-aware delivery
            $table->enum('delivery_type', [
                'home_service',
                'office_booking',
                'home_delivery',
                'outlet_pickup',
            ])->nullable()->after('shipping_method');

            // Extra cost added by selected services
            $table->decimal('service_amount', 10, 2)->default(0)->after('delivery_type');

            // Scheduled date/time for office_booking or home_service delivery slot
            $table->date('scheduled_date')->nullable()->after('service_amount');
            $table->time('scheduled_time')->nullable()->after('scheduled_date');
        });

        // Add new payment methods to payments table
        // payment_method column is currently an enum — we need to modify it
        // Laravel doesn't support enum modification well cross-platform;
        // we change it to string and validate at application level
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method', 50)->change(); // widen from enum to string
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_type', 'service_amount', 'scheduled_date', 'scheduled_time']);
        });

        Schema::dropIfExists('order_services');

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', [
                'ssl_commerz', 'bkash', 'nagad', 'cash_on_delivery', 'manual', 'bank_transfer',
            ])->change();
        });
    }
};
