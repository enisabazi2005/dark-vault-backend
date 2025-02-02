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
        Schema::create('store_passwords', function (Blueprint $table) {
            $table->id();
            $table->text('password');
            $table->timestamps();
            $table->unsignedBigInteger('dark_users_id')->nullable();
            $table->foreign('dark_users_id')
                  ->references('id')
                  ->on('dark_users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_passwords');
    }
};
