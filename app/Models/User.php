<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_admin',
        'billing_name',
        'billing_company',
        'billing_address',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_postal_code',
        'billing_country',
        'billing_tax_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Check if billing information is complete
     */
    public function hasBillingInfo()
    {
        return !empty($this->billing_address_line_1) && 
               !empty($this->billing_city) && 
               !empty($this->billing_country);
    }

    /**
     * Get formatted billing address
     */
    public function getFormattedBillingAddress()
    {
        $address = [];
        
        if ($this->billing_company) {
            $address[] = $this->billing_company;
        }
        
        if ($this->billing_address_line_1) {
            $address[] = $this->billing_address_line_1;
        }
        
        if ($this->billing_address_line_2) {
            $address[] = $this->billing_address_line_2;
        }
        
        $cityStateZip = collect([
            $this->billing_city,
            $this->billing_state,
            $this->billing_postal_code
        ])->filter()->implode(', ');
        
        if ($cityStateZip) {
            $address[] = $cityStateZip;
        }
        
        if ($this->billing_country) {
            $address[] = $this->billing_country;
        }
        
        return implode("\n", $address);
    }
}
