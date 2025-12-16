<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model 
{
    protected $table = "otp";
    
    public function getOTP() {
        return DB::table($this->table)->get();
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