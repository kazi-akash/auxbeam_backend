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
        // SMS templates table
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('type', 50); // order_confirmation, order_status, otp, delivery, etc.
            $table->text('content'); // Template with placeholders like {customer_name}, {order_number}
            $table->json('variables')->nullable(); // Available variables for this template
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // SMS logs table
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->string('phone', 20);
            $table->text('message');
            $table->string('provider', 50)->default('bulksmsbd'); // bulksmsbd, twilio
            $table->string('status', 20)->default('pending'); // pending, sent, failed, delivered
            $table->string('message_id')->nullable(); // Provider's message ID
            $table->text('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('cost', 8, 4)->nullable(); // SMS cost
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('phone');
        });

        // SMS configuration table
        Schema::create('sms_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->unique(); // bulksmsbd, twilio
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->json('credentials'); // API keys, sender ID, etc. (encrypted)
            $table->json('settings')->nullable(); // Additional settings
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms_templates');
        Schema::dropIfExists('sms_configurations');
    }
};
