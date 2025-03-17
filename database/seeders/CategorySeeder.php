<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Men
            ['name' => 'Tops', 'group' => 'Men'],
            ['name' => 'Bottoms', 'group' => 'Men'],
            ['name' => 'Coats and jackets', 'group' => 'Men'],
            ['name' => 'Jumpsuits and rompers', 'group' => 'Men'],
            ['name' => 'Footwear', 'group' => 'Men'],
            ['name' => 'Suits', 'group' => 'Men'],
            ['name' => 'Accessories', 'group' => 'Men'],
            ['name' => 'Sleepwear', 'group' => 'Men'],
            ['name' => 'Underwear', 'group' => 'Men'],
            ['name' => 'Swimwear', 'group' => 'Men'],
            ['name' => 'Costume', 'group' => 'Men'],

            // Women
            ['name' => 'Tops', 'group' => 'Women'],
            ['name' => 'Bottoms', 'group' => 'Women'],
            ['name' => 'Coats and jackets', 'group' => 'Women'],
            ['name' => 'Jumpsuits and rompers', 'group' => 'Women'],
            ['name' => 'Footwear', 'group' => 'Women'],
            ['name' => 'Suits', 'group' => 'Women'],
            ['name' => 'Accessories', 'group' => 'Women'],
            ['name' => 'Sleepwear', 'group' => 'Women'],
            ['name' => 'Underwear', 'group' => 'Women'],
            ['name' => 'Swimwear', 'group' => 'Women'],
            ['name' => 'Costume', 'group' => 'Women'],
            ['name' => 'Dresses', 'group' => 'Women'],

            // Kids
            ['name' => 'Tops', 'group' => 'Kids'],
            ['name' => 'Bottoms', 'group' => 'Kids'],
            ['name' => 'Coats and jackets', 'group' => 'Kids'],
            ['name' => 'Jumpsuits and rompers', 'group' => 'Kids'],
            ['name' => 'Footwear', 'group' => 'Kids'],
            ['name' => 'Suits', 'group' => 'Kids'],
            ['name' => 'Accessories', 'group' => 'Kids'],
            ['name' => 'Sleepwear', 'group' => 'Kids'],
            ['name' => 'Swimwear', 'group' => 'Kids'],
            ['name' => 'Costume', 'group' => 'Kids'],
            ['name' => 'Dresses', 'group' => 'Kids'],
            ['name' => 'Onesies and sleepers', 'group' => 'Kids'],
            ['name' => 'Clothing bundles', 'group' => 'Kids'],

            // Everything else
            ['name' => 'Beauty', 'group' => 'Everything else'],
            ['name' => 'Face masks and coverings', 'group' => 'Everything else'],
            ['name' => 'Home', 'group' => 'Everything else'],
            ['name' => 'Tech accessories', 'group' => 'Everything else'],
            ['name' => 'Cameras and film', 'group' => 'Everything else'],
            ['name' => 'Art', 'group' => 'Everything else'],
            ['name' => 'Books and magazines', 'group' => 'Everything else'],
            ['name' => 'Music', 'group' => 'Everything else'],
            ['name' => 'Party supplies', 'group' => 'Everything else'],
            ['name' => 'Sports equipment', 'group' => 'Everything else'],
            ['name' => 'Toys', 'group' => 'Everything else'],
            ['name' => 'Umbrellas', 'group' => 'Everything else'],
        ];

        DB::table('categories')->insert($categories);
    }
}
