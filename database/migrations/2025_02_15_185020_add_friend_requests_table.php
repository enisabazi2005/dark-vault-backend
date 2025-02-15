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
        Schema::create('friend_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dark_user_id'); // The user who received the request
            $table->string('request_friend_id', 10); // The request_id of the sender

            $table->boolean('is_accepted')->nullable(); // NULL = pending, false = rejected, true = accepted
            
            $table->json('friend')->nullable(); // Stores accepted friends
            $table->json('rejection')->nullable(); // Stores rejected friends
            $table->json('pending')->nullable(); // Stores pending friend requests

            $table->foreign('dark_user_id')->references('id')->on('dark_users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friend_requests');
    }
};
