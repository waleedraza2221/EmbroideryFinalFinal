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
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // e.g., REQ-000001
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('title'); // Brief description of the work needed
            $table->text('instructions'); // Detailed instructions from customer
            $table->json('files')->nullable(); // JSON array of uploaded files
            $table->enum('status', [
                'pending', 
                'quoted', 
                'accepted', 
                'rejected', 
                'cancelled'
            ])->default('pending');
            
            // Quote details (filled by admin)
            $table->decimal('quoted_amount', 10, 2)->nullable();
            $table->integer('delivery_days')->nullable(); // How many days to complete
            $table->text('quote_notes')->nullable(); // Admin's notes about the quote
            $table->timestamp('quoted_at')->nullable();
            
            // Customer response
            $table->timestamp('responded_at')->nullable();
            $table->text('customer_notes')->nullable(); // Customer's response notes
            
            $table->timestamps();
            
            $table->index(['customer_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
