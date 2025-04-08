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
        Schema::create('message_reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('reacted_by'); // dark_users.id
            $table->enum('reaction_type', ['heart', 'laugh', 'curious', 'like', 'dislike', 'cry']);
            $table->timestamps();
        
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->foreign('reacted_by')->references('id')->on('dark_users')->onDelete('cascade');
        
            $table->unique(['message_id', 'reacted_by']); // one reaction per user per message
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_reactions');
    }
};
