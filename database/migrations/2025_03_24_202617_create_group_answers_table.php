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
        Schema::create('group_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('group_users')->onDelete('cascade'); // Assumes you have the group_users table
            $table->foreignId('user_id')->constrained('dark_users')->onDelete('cascade');
            $table->boolean('accepted')->default(false); // False by default, meaning not accepted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_answers');
    }
};
