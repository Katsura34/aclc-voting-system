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
        // Convert year_level values to integer if needed
        $map = [
            '1st Year' => 1,
            '2nd Year' => 2,
            '3rd Year' => 3,
            '4th Year' => 4,
            '5th Year' => 5,
            '6th Year' => 6,
            '7th Year' => 7,
            '8th Year' => 8,
            '9th Year' => 9,
            '10th Year' => 10,
            '11th Year' => 11,
            '12th Year' => 12
        ];
        foreach ($map as $str => $num) {
            DB::table('candidates')->where('year_level', $str)->update(['year_level' => $num]);
        }
        // Also handle any numeric strings
        for ($i = 1; $i <= 12; $i++) {
            DB::table('candidates')->where('year_level', (string)$i)->update(['year_level' => $i]);
        }
        // Now change the column type
        Schema::table('candidates', function (Blueprint $table) {
            $table->integer('year_level')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('year_level')->nullable()->change();
        });
    }
};
