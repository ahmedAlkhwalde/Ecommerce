<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            $products = Product::factory(10)->create();
        }

        // إنشاء سلة لكل مستخدم ليس لديه سلة حالياً
        User::all()->each(function ($user) use ($products) {
            $cart = Cart::firstOrCreate([
                'user_id' => $user->id,
            ]);

            $randomProducts = $products->random(rand(1, 4));

            foreach ($randomProducts as $product) {
                $cart->products()->syncWithoutDetaching([
                    $product->id => [
                        'quantity' => rand(1, 5),
                    ],
                ]);
            }
        });
    }
}
