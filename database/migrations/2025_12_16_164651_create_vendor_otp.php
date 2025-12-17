<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_otp', function (Blueprint $table) {
            $table->id();

            // Foreign key from vendors table
            $table->foreignId('vendor_id')
                  ->constrained('vendors')
                  ->onDelete('cascade');

            $table->string('otp', 6);

            $table->enum('status', ['unverified', 'verified'])
                  ->default('unverified');

            $table->timestamp('expires_at')
                  ->default(DB::raw("CURRENT_TIMESTAMP + INTERVAL '10 minutes'"));

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_otp');
    }
};
