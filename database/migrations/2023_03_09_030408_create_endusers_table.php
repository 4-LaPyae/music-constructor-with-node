<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('endusers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('otp_code', 100)->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('auth_token', 255)->nullable();
            $table->string('token_expired_at', 255)->nullable();
            $table->text('address', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('endusers');
    }
};
