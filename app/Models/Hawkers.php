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

}