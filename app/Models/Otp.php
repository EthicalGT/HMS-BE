<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model 
{
    protected $table = "otp";
    protected $current_time;
    
    public function checkOTP($phoneno, $otp):bool {
        return DB::table($this->table)->where('hawker_mobile', $phoneno)->where('otp', $otp)->where('expires_at', '>=',now())->exists();
    }

    public function updateOTPStatus($phoneno, $otp): bool
    {
        return DB::table($this->table)
            ->where('hawker_mobile', $phoneno)
            ->where('otp', $otp)
            ->where('status', 'unverified')
            ->update(['status' => 'verified']) > 0;
    }   

    public function canRequestOTP($phoneno): bool
    {
        return DB::table($this->table)
            ->where('hawker_mobile', $phoneno)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count() === 0;
    }

    public function registerOTP(array $data) {
    try {
        DB::table($this->table)->insert($data);
        return ["success" => true];
    } catch (\Exception $e) {
        return ["success" => false, "error" => $e->getMessage()];
    }
    }

}