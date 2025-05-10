<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_message', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('group_users')->onDelete('cascade'); 
            $table->bigInteger('sent_by')->nullable();
            $table->string('message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_message');
    }
};
