<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Hawkers extends Model 
{
    protected $table = "hawkers";
    
    public function getHawkers() {
        return DB::table("users")->get();
    }

    public function registerHawkers(array $data) {
    try {
        DB::table($this->table)->insert($data);
        return ["success" => true];
    } catch (\Exception $e) {
        return ["success" => false, "error" => $e->getMessage()];
    }
}

public function retrieveHawkerPWD($email): ?string
{
    return DB::table($this->table)
        ->where('email', $email)
        ->where('status', 'active')
        ->value('password_hash');

}
public function checkHawkerPresent($email): ?bool
{
    $count = DB::table($this->table)
        ->where('email', $email)
        ->where('status', 'active')
        ->count();

    return $count > 0;
}
public function retrieveHawkerMobileNo($email): ?string{
    return DB::table($this->table)
        ->where('email', $email)
        ->where('status', 'active')
        ->value('phone_number');
}
}