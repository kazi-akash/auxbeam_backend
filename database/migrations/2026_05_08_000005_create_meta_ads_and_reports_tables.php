<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Meta Pixel configuration table
        Schema::create('meta_pixel_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('pixel_id', 50)->unique();
            $table->string('access_token')->nullable(); // For Conversion API
            $table->boolean('is_active')->default(true);
            $table->boolean('enable_pixel')->default(true); // Browser-side tracking
            $table->boolean('enable_conversion_api')->default(false); // Server-side tracking
            $table->json('events_to_track')->nullable(); // Which events to track
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // Meta Pixel events log
        Schema::create('meta_pixel_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->string('event_name', 50); // ViewContent, AddToCart, InitiateCheckout, Purchase
            $table->string('event_id', 100)->unique(); // Deduplication ID
            $table->json('event_data'); // Event parameters
            $table->string('source', 20)->default('browser'); // browser or server
            $table->boolean('sent_to_facebook')->default(false);
            $table->text('facebook_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['event_name', 'created_at']);
        });

        // Add cost_price to order_items for profit calculation
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable()->after('unit_price');
        });

        // WhatsApp configuration and logs
        Schema::create('whatsapp_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->default('whatsapp_business'); // whatsapp_business, twilio
            $table->boolean('is_active')->default(false);
            $table->json('credentials'); // API credentials (encrypted)
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->string('phone', 20);
            $table->text('message');
            $table->string('message_type', 50)->default('text'); // text, template, media
            $table->string('template_name')->nullable();
            $table->string('status', 20)->default('pending'); // pending, sent, delivered, read, failed
            $table->string('message_id')->nullable();
            $table->text('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // EMI support - add to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('is_emi')->default(false)->after('payment_method');
            $table->integer('emi_months')->nullable()->after('is_emi'); // 3, 6, 12
            $table->decimal('emi_amount', 10, 2)->nullable()->after('emi_months');
            $table->decimal('emi_interest_rate', 5, 2)->nullable()->after('emi_amount');
            $table->decimal('emi_total_amount', 10, 2)->nullable()->after('emi_interest_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['is_emi', 'emi_months', 'emi_amount', 'emi_interest_rate', 'emi_total_amount']);
        });

        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('whatsapp_configurations');
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
        Schema::dropIfExists('meta_pixel_events');
        Schema::dropIfExists('meta_pixel_configurations');
    }
};
