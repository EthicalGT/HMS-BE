<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class Vendor extends Model
{
    protected $table = 'vendors';
    
    public function getVendor() {
        return DB::table($this->table)->get();
    }

    public function registerVendor(array $data) {
        try {
            DB::table($this->table)->insert($data);
            return ["success" => true];
        } catch (\Exception $e) {
            return ["success" => false, "error" => $e->getMessage()];
        }
    }
    public function retrieveVendorPWD($email): ?string
    {
        return DB::table($this->table)
            ->where('email', $email)
            ->where('status', 'active')
            ->value('password');

    }
    public function checkVendorPresent($email): ?bool
    {
        $count = DB::table($this->table)
            ->where('email', $email)
            ->where('status', 'active')
            ->count();

        return $count > 0;
    }
}
