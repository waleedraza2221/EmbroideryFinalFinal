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
        if (!$this->isQuoted()) {
            throw new \Exception('Cannot create order from unquoted request');
        }

        return Order::create([
            'quote_request_id' => $this->id,
            'customer_id' => $this->customer_id,
            'title' => $this->title,
            'instructions' => $this->instructions,
            'original_files' => $this->files,
            'amount' => $this->quoted_amount,
            'delivery_days' => $this->delivery_days,
            'status' => 'active'
        ]);
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
