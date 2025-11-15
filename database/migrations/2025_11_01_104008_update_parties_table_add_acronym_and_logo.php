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
        Schema::table('parties', function (Blueprint $table) {
            $table->string('acronym', 10)->nullable()->after('name');
            $table->string('logo')->nullable()->after('description');
        });

        // For SQLite compatibility, drop slug column if it exists
        try {
            if (Schema::hasColumn('parties', 'slug')) {
                Schema::table('parties', function (Blueprint $table) {
                    $table->dropColumn('slug');
                });
            }
        } catch (\Exception $e) {
            // Column doesn't exist or can't be dropped, that's OK
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->dropColumn(['acronym', 'logo']);
        });
    }
};
