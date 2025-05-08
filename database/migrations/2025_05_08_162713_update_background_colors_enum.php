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
        \DB::statement("ALTER TABLE background_colors MODIFY `option` ENUM(
            'purple_white',
            'blue_white',
            'green_black',
            'red_black',
            'gray_black',
            'galaxy',
            'supernova',
            'heart_nebula',
            'sunset_vibe',
            'northern_lights',
            'cosmic_fusion'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE background_colors MODIFY `option` ENUM(
            'purple_white',
            'blue_white',
            'green_black',
            'red_black',
            'gray_black'
        ) NOT NULL");        
    }
};
