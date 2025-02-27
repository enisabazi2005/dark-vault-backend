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
        Schema::create('user_mutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dark_users_id')
            ->constrained('dark_users')
            ->onDelete('cascade');
             $table->foreignId('muted_id')
            ->constrained('dark_users')
            ->onDelete('cascade');
             $table->boolean('muted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_mutes');
    }
};
