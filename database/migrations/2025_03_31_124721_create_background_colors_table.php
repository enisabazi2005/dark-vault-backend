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
        Schema::create('background_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dark_users_id')->constrained('dark_users')->onDelete('cascade');
            $table->enum('option', ['purple_white', 'blue_white', 'green_black', 'red_black', 'gray_black'])
                ->default('blue_white'); 
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('background_colors');
    }
};
