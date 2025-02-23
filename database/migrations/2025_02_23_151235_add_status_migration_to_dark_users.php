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
        Schema::table('dark_users', function (Blueprint $table) {
            $table->boolean('online')->default(false);
            $table->boolean('offline')->default(false);
            $table->boolean('away')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dark_users', function (Blueprint $table) {
            $table->dropColumn(['online', 'offline', 'away']);
        });
    }
};
