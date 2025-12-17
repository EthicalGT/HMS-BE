<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class VendorOtp extends Model
{
    use HasFactory;

    protected $table = 'vendor_otp';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'vendor_id',
        'otp',
        'status',
        'expires_at',
    ];

    /**
     * Type casting
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Relationship
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Scope: valid (not expired) OTPs
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
                     ->where('status', 'unverified');
    }
}
