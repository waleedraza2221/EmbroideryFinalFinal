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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g., ORD-000001
            $table->foreignId('quote_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('title'); // Copied from quote request
            $table->text('instructions'); // Original instructions
            $table->json('original_files')->nullable(); // Original files from request
            
            // Order details
            $table->decimal('amount', 10, 2); // Final agreed amount
            $table->integer('delivery_days'); // Agreed delivery time
            $table->date('due_date'); // Calculated due date
            
            $table->enum('status', [
                'active',      // Order is being worked on
                'delivered',   // Admin has delivered work
                'revision',    // Customer requested revision
                'completed',   // Customer accepted delivery
                'cancelled'    // Order was cancelled
            ])->default('active');
            
            // Delivery
            $table->json('delivery_files')->nullable(); // Files delivered by admin
            $table->text('delivery_notes')->nullable(); // Admin's delivery notes
            $table->timestamp('delivered_at')->nullable();
            
            // Customer feedback
            $table->text('customer_feedback')->nullable();
            $table->integer('rating')->nullable(); // 1-5 star rating
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['customer_id', 'status']);
            $table->index('status');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
