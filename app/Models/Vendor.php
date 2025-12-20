<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendors';

    // Email as Primary Key
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';

    // DB manages timestamps
    public $timestamps = false;

    protected $fillable = [
        'email',
        'fullname',
        'contact_no',
        'aadhaar_number',
        'firm_name',
        'product_category',
        'firm_addr',
        'password',
        'status',
        'city',
        'state',
        'pincode',
        'gstin_no',
        'profile_photo_url',
        'aadhaar_verified',
        'role'
    ];

    protected $hidden = [
        'password'
    ];
}
