<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'quote_request_id',
        'customer_id',
        'title',
        'instructions',
        'original_files',
        'amount',
        'delivery_days',
        'due_date',
        'status',
        'delivery_files',
        'delivery_notes',
        'delivered_at',
        'customer_feedback',
        'rating',
        'completed_at'
    ];

    protected $casts = [
        'original_files' => 'array',
        'delivery_files' => 'array',
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Generate unique order number and set due date
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $lastOrder = static::latest('id')->first();
                $nextNumber = $lastOrder ? $lastOrder->id + 1 : 1;
                $order->order_number = 'ORD-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }
            
            // Ensure delivery_days defaults to 1 (24 hours)
            if (empty($order->delivery_days)) {
                $order->delivery_days = 1;
            }
            
            // Calculate due date - always set to 24 hours from now as minimum
            if (empty($order->due_date)) {
                $deliveryDays = max(1, $order->delivery_days); // Minimum 1 day (24 hours)
                $order->due_date = Carbon::now()->addDays($deliveryDays);
            }
        });
    }

    /**
     * Get the customer who owns this order
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the quote request this order was created from
     */
    public function quoteRequest()
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    /**
     * Get the invoice for this order
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Check if order is overdue
     */
    public function isOverdue()
    {
        return $this->status === 'active' && Carbon::now()->isAfter($this->due_date);
    }

    /**
     * Check if order is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if order is delivered
     */
    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if order is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Get days remaining until due date
     */
    public function getDaysRemainingAttribute()
    {
        if ($this->isCompleted()) return null;
        
        $now = Carbon::now();
        return $now->diffInDays($this->due_date, false);
    }

    /**
     * Generate invoice for completed order
     */
    public function generateInvoice()
    {
        // Don't generate duplicate invoices
        if ($this->invoice) {
            return $this->invoice;
        }

        // Only generate invoices for completed orders
        if (!$this->isCompleted()) {
            return null;
        }

        // Get customer billing information
        $customer = $this->customer;

        // Create line items from order details
        $lineItems = [
            [
                'description' => $this->title,
                'quantity' => 1,
                'price' => $this->amount
            ]
        ];

        // Calculate tax (you can adjust this logic)
        $subtotal = $this->amount;
        $taxRate = 0.10; // 10% tax - adjust as needed
        $taxAmount = $subtotal * $taxRate;
        $totalAmount = $subtotal + $taxAmount;

        // Create the invoice with your model's field structure
        $invoice = Invoice::create([
            'customer_id' => $this->customer_id,
            'order_id' => $this->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'billing_address' => $customer->billing_address ?: ($customer->billing_name ? 
                $customer->billing_name . "\n" . 
                ($customer->billing_company ? $customer->billing_company . "\n" : '') .
                ($customer->billing_address ?: 'Address not provided') . "\n" .
                ($customer->billing_city ?: 'City not provided') . ", " .
                ($customer->billing_state ?: 'State not provided') . " " .
                ($customer->billing_zip ?: '00000') . "\n" .
                ($customer->billing_country ?: 'Country not provided')
                : 'Address not provided'),
            'billing_company' => $customer->billing_company,
            'billing_tax_id' => null,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => 'USD',
            'status' => 'sent', // Set as sent since it's generated automatically
            'invoice_date' => now(),
            'due_date' => now()->addDays(30), // 30 days to pay
            'line_items' => $lineItems,
            'notes' => 'Invoice for completed embroidery order: ' . $this->order_number
        ]);

        return $invoice;
    }
}
