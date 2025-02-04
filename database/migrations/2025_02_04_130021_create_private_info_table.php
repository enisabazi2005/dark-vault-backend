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
        Schema::create('private_info', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('info-1');
            $table->string('info-2')->nullable();
            $table->string('info-3')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('dark_users_id')->nullable();
            $table->foreign('dark_users_id')
                  ->references('id')
                  ->on('dark_users')
                  ->after('id')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('private_info');
    }
};
