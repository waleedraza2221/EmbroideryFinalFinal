<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'order_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'billing_address',
        'billing_company',
        'billing_tax_id',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'currency',
        'status',
        'invoice_date',
        'due_date',
        'sent_at',
        'paid_at',
        'notes',
        'line_items'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'line_items' => 'array'
    ];

    /**
     * Boot method to auto-generate invoice number
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $lastInvoice = static::latest('id')->first();
                $nextNumber = $lastInvoice ? $lastInvoice->id + 1 : 1;
                $invoice->invoice_number = 'INV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the order that owns the invoice
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the customer that owns the invoice
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue()
    {
        return $this->status !== 'paid' && $this->due_date < now()->toDateString();
    }

    /**
     * Check if invoice is draft
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    /**
     * Get formatted invoice number
     */
    public function getFormattedInvoiceNumber()
    {
        return $this->invoice_number;
    }

    /**
     * Get status badge color
     */
    public function getStatusColor()
    {
        return match($this->status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'paid' => 'green',
            'overdue' => 'red',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('invoice_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('invoice_date', '<=', $to);
        }
        return $query;
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope for customer invoices
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
