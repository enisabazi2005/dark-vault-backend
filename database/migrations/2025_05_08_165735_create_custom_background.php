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
        Schema::create('custom_background', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dark_users_id')->constrained('dark_users')->onDelete('cascade');
            $table->string('color_1')->nullable();
            $table->string('color_2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_background');
    }
};
