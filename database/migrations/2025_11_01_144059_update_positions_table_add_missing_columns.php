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
        Schema::table('positions', function (Blueprint $table) {
            // Add description column
            $table->text('description')->nullable()->after('name');
            
            // Rename max_winners to max_votes
            $table->renameColumn('max_winners', 'max_votes');
            
            // Rename order to display_order
            $table->renameColumn('order', 'display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            // Drop description column
            $table->dropColumn('description');
            
            // Rename back max_votes to max_winners
            $table->renameColumn('max_votes', 'max_winners');
            
            // Rename back display_order to order
            $table->renameColumn('display_order', 'order');
        });
    }
};
