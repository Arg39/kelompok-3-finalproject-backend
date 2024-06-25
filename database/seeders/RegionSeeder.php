<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::unprepared(file_get_contents('database/sql/wilayah_indonesia.sql'));
        $timestamp = Carbon::now();

        DB::table('provinces')->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::table('regencies')->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
