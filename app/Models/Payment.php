<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_request_id',
        'customer_id',
        'payment_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_data',
        'transaction_data',
        'paid_at'
    ];

    protected $casts = [
        'payment_data' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    /**
     * Get the quote request that owns the payment
     */
    public function quoteRequest()
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    /**
     * Get the customer that owns the payment
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment failed
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }
}
