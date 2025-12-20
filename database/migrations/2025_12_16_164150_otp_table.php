<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('otp',6);

            // 👇 Identify user
            $table->enum('user_type', ['vendor', 'hawker']);

            // 👇 FK for Vendor (email)
            $table->string('vendor_email', 150)->nullable();

            // 👇 FK for Hawker (mobile)
            $table->string('hawker_mobile', 15)->nullable();

            $table->enum('status', ['unverified', 'verified'])
                  ->default('unverified');

            $table->timestamp('created_at')
                  ->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->timestamp('expires_at')
                  ->default(DB::raw("CURRENT_TIMESTAMP + INTERVAL '10 minutes'"));

            // ✅ Foreign Keys
            $table->foreign('vendor_email')
                  ->references('email')
                  ->on('vendors')
                  ->onDelete('cascade');

            $table->foreign('hawker_mobile')
                  ->references('phone_number')
                  ->on('hawkers')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp');
    }
};
