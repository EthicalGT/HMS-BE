<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();

            // Basic details
            $table->string('name', 150);
            $table->string('contact_person', 150);
            $table->string('phone', 15)->unique();
            $table->string('email', 150)->unique();

            // Authentication
            $table->string('password');

            // Identity
            $table->string('aadhaar_no', 12)->unique();

            // Address details
            $table->string('address_line1', 255);
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('pincode', 10);
            $table->string('country', 100)->default('India');

            // Business details
            $table->string('gst_no', 20)->nullable()->unique();

            // Image
            $table->string('image')->nullable();

            // Status
            $table->enum('status', ['active', 'inactive'])
                  ->default('inactive');

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
