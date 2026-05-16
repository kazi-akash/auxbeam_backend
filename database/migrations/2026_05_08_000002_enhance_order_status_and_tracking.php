<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update orders table with new statuses and tracking fields
        Schema::table('orders', function (Blueprint $table) {
            // Add new order source tracking
            $table->enum('order_source', [
                'website',
                'facebook',
                'instagram',
                'whatsapp',
                'phone_call',
                'manual'
            ])->default('website')->after('order_type');

            // Add UTM tracking fields
            $table->string('utm_source')->nullable()->after('order_source');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('utm_content')->nullable()->after('utm_campaign');
            $table->string('utm_term')->nullable()->after('utm_content');
            $table->text('referrer_url')->nullable()->after('utm_term');

            // Add follow-up reminder
            $table->timestamp('follow_up_at')->nullable()->after('notes');
            $table->boolean('follow_up_completed')->default(false)->after('follow_up_at');
        });

        // Modify status enum to include new statuses
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'confirmed',
            'processing',
            'incomplete',
            'good_but_no_response',
            'advance_payment',
            'on_hold',
            'ready_to_ship',
            'shipped',
            'complete',
            'cancelled',
            'return_requested',
            'return_approved',
            'refunded'
        ) DEFAULT 'pending'");

        // Create order_notes table for detailed note tracking
        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('note');
            $table->enum('note_type', ['internal', 'customer', 'system'])->default('internal');
            $table->boolean('is_customer_notified')->default(false);
            $table->timestamps();

            $table->index('order_id');
            $table->index('created_at');
        });

        // Create order_reminders table
        Schema::create('order_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('remind_at');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('remind_at');
            $table->index('is_completed');
        });

        // Create order_status_history table for audit trail
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_reminders');
        Schema::dropIfExists('order_notes');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_source',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'utm_term',
                'referrer_url',
                'follow_up_at',
                'follow_up_completed',
            ]);
        });

        // Revert status enum to original
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'confirmed',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
            'return_requested',
            'return_approved',
            'refunded'
        ) DEFAULT 'pending'");
    }
};
