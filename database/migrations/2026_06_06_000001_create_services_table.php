<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // "Home Service", "Office Booking", etc.
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', [
                'home_service',
                'office_booking',
                'home_delivery',
                'outlet_pickup',
            ]);
            $table->boolean('requires_scheduling')->default(false); // office booking needs date/time
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Pivot: which services are available per product, with per-product pricing
        Schema::create('product_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2)->default(0);   // extra charge for this service on this product
            $table->boolean('is_required')->default(false); // if true, must add this service to buy product
            $table->boolean('is_active')->default(true);
            $table->text('conditions')->nullable();         // any conditions text
            $table->timestamps();

            $table->unique(['product_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_services');
        Schema::dropIfExists('services');
    }
};
