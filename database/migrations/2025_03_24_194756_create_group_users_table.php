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
        Schema::create('group_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dark_user_id')->constrained('dark_users')->onDelete('cascade'); // Group creator
            $table->string('title')->nullable();
            $table->string('code')->unique();
            $table->json('users_in_group')->nullable();
            $table->json('users_invited')->nullable();
            $table->json('users_answered')->nullable();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('semi_admin_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_users');
    }
};
