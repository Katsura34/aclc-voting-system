<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->string('acronym', 10)->after('name');
            $table->string('logo')->nullable()->after('color');
        });
        
        // For SQLite, we need to recreate the table without the slug column
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support dropping columns directly in older versions
            // We'll handle this differently for SQLite
            DB::statement('PRAGMA foreign_keys=off;');
            
            // Create new table
            Schema::create('parties_new', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('acronym', 10);
                $table->string('color')->nullable();
                $table->string('logo')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
            
            // Copy data
            DB::statement('INSERT INTO parties_new (id, name, acronym, color, logo, description, created_at, updated_at) SELECT id, name, acronym, color, logo, description, created_at, updated_at FROM parties');
            
            // Drop old table and rename new one
            Schema::drop('parties');
            Schema::rename('parties_new', 'parties');
            
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            Schema::table('parties', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
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
