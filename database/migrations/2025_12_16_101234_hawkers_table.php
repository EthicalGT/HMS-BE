<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hawkers', function (Blueprint $table) {
            $table->bigIncrements('hawker_id');

            $table->string('full_name', 150);
            $table->string('phone_number', 15)->unique();
            $table->string('email', 150)->unique()->nullable();

            $table->text('password_hash')->nullable();

            $table->string('aadhaar_number', 12)->unique()->nullable();

            $table->enum('aadhaar_verified', ['unverified', 'verified'])
                  ->default('unverified');

            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->text('profile_photo_url')->nullable();
            $table->string('role', 20)->default('hawker');
            $table->string('status', 20)->default('active');

            $table->timestamp('created_at')
                  ->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->timestamp('updated_at')
                  ->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hawkers');
        DB::statement("DROP TYPE IF EXISTS verification_status");
    }
};
