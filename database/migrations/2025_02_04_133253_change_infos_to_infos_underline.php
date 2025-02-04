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
        Schema::table('private_info', function (Blueprint $table) {
            if(Schema::hasColumn('private_info', 'info-1')) { 
                $table->dropColumn('info-1');
            }
            if(Schema::hasColumn('private_info', 'info-2')) { 
                $table->dropColumn('info-2');
            }
            if(Schema::hasColumn('private_info', 'info-3')) { 
                $table->dropColumn('info-3');
            }
            $table->string('info_1');
            $table->string('info_2');
            $table->string('info_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('private_info', function (Blueprint $table) {
            //
        });
    }
};
