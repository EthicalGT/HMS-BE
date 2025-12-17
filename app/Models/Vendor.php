<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Vendor extends Authenticatable
{
    use HasFactory;

    protected $table = 'vendors';

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'password',
        'aadhaar_no',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'pincode',
        'country',
        'gst_no',
        'image',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * 🔐 Automatically hash password when set
     */
    public function setPasswordAttribute($value)
    {
        // Avoid double hashing
        if (!Hash::needsRehash($value)) {
            $this->attributes['password'] = $value;
        } else {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    /**
     * Relationships
     */
    public function otps()
    {
        return $this->hasMany(VendorOtp::class);
    }
}
