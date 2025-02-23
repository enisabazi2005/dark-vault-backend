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
            $table->boolean('do_not_disturb')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dark_users', function (Blueprint $table) {
            $table->dropColumn('do_not_disturb');
        });
    }
};
