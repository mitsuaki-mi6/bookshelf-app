<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); // NULL許可
            $table->string('password');
            $table->text('two_factor_secret')->nullable();       // 基本機能版用のカラム（追加）
            $table->text('two_factor_recovery_codes')->nullable(); // 基本機能版用のカラム（追加）
            $table->timestamp('two_factor_confirmed_at')->nullable(); // 基本機能版用のカラム（追加）
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
