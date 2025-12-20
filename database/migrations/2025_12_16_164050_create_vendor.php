<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {

            // PRIMARY KEY
            $table->string('email', 150)->primary();

            $table->string('fullname', 150)->nullable();

            // UNIQUE ONLY
            $table->string('contact_no', 15)->unique()->nullable();
            $table->string('aadhaar_number', 12)->unique()->nullable();

            $table->string('firm_name', 150)->nullable();

            $table->enum('product_category', [
                'vegetables',
                'beverage',
                'street_food',
                'snacks',
                'fruits',
                'dairy_product',
                'bakery_item',
                'meat_seafood',
                'flowers',
                'groceries',
                'sweet',
                'spices'
            ]);

            $table->text('firm_addr')->nullable();

            $table->string('password')->nullable();

            $table->string('status')->default('active');

            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('gstin_no', 15)->nullable();

            $table->text('profile_photo_url')->nullable();

            $table->enum('aadhaar_verified', ['unverified', 'verified'])
                  ->default('unverified');

            $table->string('role', 20)->default('vendor');

            // TIMESTAMPS
            $table->timestamp('created_at')
                  ->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->timestamp('updated_at')
                  ->default(DB::raw('CURRENT_TIMESTAMP'))
                  ->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
