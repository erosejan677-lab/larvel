<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            // International Brands
            ['name' => 'Gucci'],
            ['name' => 'Balenciaga'],
            ['name' => 'Nike'],
            ['name' => 'Adidas'],
            ['name' => 'The North Face'],
            ['name' => 'Patagonia'],
            ['name' => 'Zara'],
            ['name' => 'H&M'],
            ['name' => 'Christian Louboutin'],
            ['name' => 'Jimmy Choo'],
            ['name' => 'Tom Ford'],
            ['name' => 'Hugo Boss'],
            ['name' => 'Ray-Ban'],
            ['name' => 'Michael Kors'],
            ['name' => 'Calvin Klein'],
            ['name' => 'Victoria’s Secret'],
            ['name' => 'Speedo'],
            ['name' => 'Billabong'],
            ['name' => 'Spirit Halloween'],
            ['name' => 'Rubie’s'],
            ['name' => 'Dior'],
            ['name' => 'Chanel'],
            ['name' => 'Gerber'],
            ['name' => 'Carter’s'],
            ['name' => 'Boohoo'],
            ['name' => 'ASOS'],
            ['name' => 'Fenty Beauty'],
            ['name' => 'Maybelline'],
            ['name' => 'Etsy'],
            ['name' => 'Uniqlo'],
            ['name' => 'Ralph Lauren Home'],
            ['name' => 'Versace Home'],
            ['name' => 'Casetify'],
            ['name' => 'OtterBox'],
            ['name' => 'Canon'],
            ['name' => 'Polaroid'],
            ['name' => 'Banksy'],
            ['name' => 'MoMA'],
            ['name' => 'Vogue'],
            ['name' => 'National Geographic'],
            ['name' => 'Fender'],
            ['name' => 'Gibson'],
            ['name' => 'Party City'],
            ['name' => 'Meri Meri'],
            ['name' => 'Wilson'],
            ['name' => 'LEGO'],
            ['name' => 'Hasbro'],
            ['name' => 'Totes'],
            ['name' => 'Blunt Umbrellas'],

            // Pakistani Brands
            ['name' => 'Khaadi'],
            ['name' => 'Gul Ahmed'],
            ['name' => 'Sana Safinaz'],
            ['name' => 'Alkaram Studio'],
            ['name' => 'Nishat Linen'],
            ['name' => 'J. (Junaid Jamshed)'],
            ['name' => 'Maria B.'],
            ['name' => 'Asim Jofa'],
            ['name' => 'Élan'],
            ['name' => 'Sapphire'],
            ['name' => 'Limelight'],
            ['name' => 'Bonanza Satrangi'],
            ['name' => 'Zellbury'],
            ['name' => 'Beechtree'],
            ['name' => 'Ethnic by Outfitters'],
            ['name' => 'Outfitters'],
            ['name' => 'Generation'],
            ['name' => 'Cross Stitch'],
            ['name' => 'Mausummery'],
            ['name' => 'Bareeze'],
            ['name' => 'HSY (Hassan Sheheryar Yasin)'],
            ['name' => 'Deepak Perwani'],
            ['name' => 'Amir Adnan'],
            ['name' => 'Edenrobe'],
            ['name' => 'Charcoal'],
            ['name' => 'Cougar'],
            ['name' => 'Leisure Club'],
            ['name' => 'Servis Shoes'],
            ['name' => 'Bata Pakistan'],
            ['name' => 'Stylo'],
            ['name' => 'Borjan'],
            ['name' => 'Metro Shoes'],
            ['name' => 'Ndure'],
            ['name' => 'Insignia'],
            ['name' => 'Lark & Finch'],
            ['name' => 'Kapray'],
            ['name' => 'Unze London Pakistan'],
            ['name' => 'Rici Melion'],
            ['name' => 'Cambridge'],
            ['name' => 'Royal Tag'],
            ['name' => 'Imperial'],
            ['name' => 'Diners'],
            ['name' => 'Idea Pret'],
            ['name' => 'Rang Ja'],
            ['name' => 'Miniso Pakistan'],
            ['name' => 'Saadia Asad'],
            ['name' => 'Ghazal'],
            ['name' => 'Zeen'],
            ['name' => 'Breakout'],
            ['name' => 'Almirah'],
        ];

        DB::table('brands')->insert($brands);
    }
}
