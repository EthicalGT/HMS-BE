<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TYPE otp_status AS ENUM ('unverified', 'verified')
        ");

        Schema::create('otp', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('otp', 6);
            $table->string('email', 150);

            $table->enum('status', ['unverified', 'verified'])
                  ->default('unverified');

            $table->timestamp('created_at')
                  ->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->timestamp('expires_at')
                  ->default(DB::raw("CURRENT_TIMESTAMP + INTERVAL '10 minutes'"));

            $table->foreign('email')
                  ->references('email')
                  ->on('hawkers')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp');
        DB::statement("DROP TYPE IF EXISTS otp_status");
    }
};
