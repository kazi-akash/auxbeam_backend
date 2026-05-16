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
        // Customer segments table
        Schema::create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // VIP, Repeat Customer, COD Risk, etc.
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#3B82F6'); // For UI display
            $table->string('icon', 50)->nullable(); // Icon class name
            $table->json('criteria')->nullable(); // Segmentation criteria as JSON
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Display priority
            $table->timestamps();
        });

        // Customer tags table
        Schema::create('customer_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#10B981');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Customer segment assignments (many-to-many)
        Schema::create('customer_segment_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_segment_id')->constrained('customer_segments')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->string('assigned_by', 50)->default('system'); // system or admin_id
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'customer_segment_id']);
        });

        // Customer tag assignments (many-to-many)
        Schema::create('customer_tag_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_tag_id')->constrained('customer_tags')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['user_id', 'customer_tag_id']);
        });

        // Customer analytics summary table
        Schema::create('customer_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            
            // Order statistics
            $table->integer('total_orders')->default(0);
            $table->integer('completed_orders')->default(0);
            $table->integer('cancelled_orders')->default(0);
            $table->integer('returned_orders')->default(0);
            
            // Financial statistics
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->decimal('average_order_value', 10, 2)->default(0);
            $table->decimal('lifetime_value', 12, 2)->default(0);
            
            // COD statistics
            $table->integer('cod_orders')->default(0);
            $table->integer('cod_completed')->default(0);
            $table->integer('cod_cancelled')->default(0);
            $table->decimal('cod_success_rate', 5, 2)->default(0); // Percentage
            
            // Online payment statistics
            $table->integer('online_payment_orders')->default(0);
            $table->integer('online_payment_completed')->default(0);
            
            // Engagement metrics
            $table->timestamp('first_order_at')->nullable();
            $table->timestamp('last_order_at')->nullable();
            $table->integer('days_since_last_order')->nullable();
            $table->integer('order_frequency_days')->nullable(); // Average days between orders
            
            // Risk scoring
            $table->decimal('risk_score', 5, 2)->default(0); // 0-100 scale
            $table->string('risk_level', 20)->default('low'); // low, medium, high
            
            // VIP scoring
            $table->decimal('vip_score', 5, 2)->default(0); // 0-100 scale
            $table->boolean('is_vip')->default(false);
            
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_analytics');
        Schema::dropIfExists('customer_tag_assignments');
        Schema::dropIfExists('customer_segment_assignments');
        Schema::dropIfExists('customer_tags');
        Schema::dropIfExists('customer_segments');
    }
};
