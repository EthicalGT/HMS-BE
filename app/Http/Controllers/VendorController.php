<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;

class VendorController extends Controller
{
    /**
     * Return all vendors
     */
    public function index()
    {
        try {
            $vendors = Vendor::all(); // Fetch all vendors

            return response()->json([
                'success' => true,
                'count'   => $vendors->count(),
                'data'    => $vendors
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: '.$e->getMessage(),
            ], 500);
        }
    }
}
