<?php

use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [];
        $products[0] = ['name' => 'Nivea Men Deep Impact Scalp Clean Shampoo', 'image' => 'https://rukminim1.flixcart.com/image/832/832/jgpfs7k0/shampoo/h/f/e/250-deep-impact-shampoo-nivea-men-original-imaf4se4yzrdqsyj.jpeg?q=70', 'price' => '150'];
        $products[1] = ['name' => 'LOREM LK-05 Black-Round Professional Plain Leather Watch', 'image' => 'https://rukminim1.flixcart.com/image/832/832/jhtg4280/watch/h/t/h/lk-05-lorem-original-imaf5r3qumfpe3k7.jpeg?q=70', 'price' => '2000'];
        $products[2] = ['name' => 'Right Choice (2077) BLACK RED', 'image' => 'https://rukminim1.flixcart.com/image/832/832/jmawvbk0/backpack/f/p/u/right-choice-2077-black-red-stylish-tuff-quality-college-school-original-imaf98qhzfkzzgcw.jpeg?q=70', 'price' => '450'];
        $products[3] = ['name' => 'MarQ by Flipkart 7.5 kg Fully Automatic Front Load Washing Machine ', 'image' => 'https://rukminim1.flixcart.com/image/832/832/jka1evk0/washing-machine-new/f/6/u/mqflxi75-marq-by-flipkart-original-imaf7z3tn7fgswfg.jpeg?q=70', 'price' => '150'];
        $products[4] = ['name' => 'Honor 7S (Black, 16 GB)', 'image' => 'https://rukminim1.flixcart.com/image/832/832/jll6xzk0/mobile/3/g/g/honor-7s-na-original-imaf8zwg9kbzurst.jpeg?q=70', 'price' => '150'];
        $products[5] = ['name' => 'iVooMi Z1 (Ocean Blue, 16 GB)', 'image' => 'https://rukminim1.flixcart.com/image/832/832/jmz7csw0/mobile/f/y/z/ivoomi-z1-iv104-original-imaf9nyqvb7bg6cx.jpeg?q=70', 'price' => '150'];

        for ($i = 0; $i < count($products); $i++) {
            DB::table('products')->insert($products[$i]);
        }
    }
}
