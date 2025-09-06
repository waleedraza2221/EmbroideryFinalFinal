<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'customer_id',
        'title',
        'instructions',
        'files',
        'status',
        'quoted_amount',
        'delivery_days',
        'quote_notes',
        'quoted_at',
        'responded_at',
        'customer_notes'
    ];

    protected $casts = [
        'files' => 'array',
        'quoted_amount' => 'decimal:2',
        'quoted_at' => 'datetime',
        'responded_at' => 'datetime'
    ];

    /**
     * Generate unique request number
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($quoteRequest) {
            if (empty($quoteRequest->request_number)) {
                $lastRequest = static::latest('id')->first();
                $nextNumber = $lastRequest ? $lastRequest->id + 1 : 1;
                $quoteRequest->request_number = 'REQ-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the customer who made this request
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the order created from this quote request
     */
    public function order()
    {
        return $this->hasOne(Order::class);
    }

    /**
     * Check if quote has been accepted
     */
    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if quote is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if quote has been provided by admin
     */
    public function isQuoted()
    {
        return $this->status === 'quoted';
    }

    /**
     * Create an order from this quote request
     */
    public function createOrder()
    {
        // Allow creation if quote is quoted or already accepted (payment flow may accept first)
        if (!($this->isQuoted() || $this->isAccepted())) {
            throw new \Exception('Cannot create order from request that is neither quoted nor accepted');
        }

        // Check if order already exists to prevent duplicates (direct query to be sure)
        $existingOrder = Order::where('quote_request_id', $this->id)->first();
        if ($existingOrder) {
            return $existingOrder;
        }

        try {
            return Order::create([
                'quote_request_id' => $this->id,
                'customer_id' => $this->customer_id,
                'title' => $this->title,
                'instructions' => $this->instructions,
                'original_files' => $this->files,
                'amount' => $this->quoted_amount,
                'delivery_days' => $this->delivery_days ?: 1, // Default to 1 day (24 hours) if not set
                'status' => 'active'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // If unique constraint violation, fetch and return the existing order
            if (str_contains($e->getMessage(), 'orders_quote_request_id_unique')) {
                return Order::where('quote_request_id', $this->id)->first();
            }
            throw $e; // Re-throw other exceptions
        }
    }

    /**
     * Get the payments for this quote request
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the latest payment for this quote request
     */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    /**
     * Check if quote has been paid for
     */
    public function isPaid()
    {
        return $this->payments()->where('status', 'completed')->exists();
    }
}
