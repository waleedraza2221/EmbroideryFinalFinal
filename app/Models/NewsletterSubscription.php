<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email','ip','user_agent','is_active','unsubscribed_at','confirmation_token','confirmed_at'
    ];

    protected $casts = [
        'is_active'=>'boolean',
        'unsubscribed_at'=>'datetime',
        'confirmed_at'=>'datetime'
    ];

    public function scopeConfirmed($q){ return $q->whereNotNull('confirmed_at'); }

    public function markConfirmed(): void
    {
        $this->confirmed_at = now();
        $this->confirmation_token = null;
        $this->save();
    }
}
