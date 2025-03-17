<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditions = [
            ['title' => 'Brand new', 'description' => 'Unused with original packaging and tags'],
            ['title' => 'Like new', 'description' => 'Mint condition pre-owned or new without tags'],
            ['title' => 'Used - Excellent', 'description' => 'Lightly used but no noticeable flaws'],
            ['title' => 'Used - Good', 'description' => 'Minor flaws or signs of wear, to be noted in the description or photos'],
            ['title' => 'Used - Fair', 'description' => 'Obvious flaws or signs of wear, to be noted in the description or photos']
        ];

        DB::table('conditions')->insert($conditions);
    }
}
