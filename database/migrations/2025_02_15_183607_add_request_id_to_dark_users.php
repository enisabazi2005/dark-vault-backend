<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dark_users', function (Blueprint $table) {
            $table->string('request_id', 10)->nullable()->after('id'); // Add column without unique constraint
        });

        // Generate unique request_id for existing users
        foreach (DB::table('dark_users')->get() as $user) {
            do {
                $requestId = strtoupper(Str::random(7));
            } while (DB::table('dark_users')->where('request_id', $requestId)->exists());

            DB::table('dark_users')->where('id', $user->id)->update(['request_id' => $requestId]);
        }

        Schema::table('dark_users', function (Blueprint $table) {
            $table->string('request_id', 10)->unique()->nullable()->change(); // Now apply unique constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dark_users', function (Blueprint $table) {
            $table->dropUnique(['request_id']);
            $table->dropColumn('request_id');
        });
    }
};
