<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path disesuaikan dengan file di public/img/
        Menu::create(['namaMenu' => 'Cheesecake', 'kategori' => 'Main Menu', 'harga' => 35000, 'image_url' => 'img/Cheesecake.JPEG']);
        Menu::create(['namaMenu' => 'Dulce Chocolat Black Truffle Cheesecake', 'kategori' => 'Main Menu', 'harga' => 58000, 'image_url' => 'img/Dulce.JPG']);
        Menu::create(['namaMenu' => 'Chocolat Mousse', 'kategori' => 'Main Menu', 'harga' => 25000, 'image_url' => 'img/Chocolate Mousse.JPG']);

        Menu::create(['namaMenu' => 'Matcha Latte', 'kategori' => 'Beverage', 'harga' => 74000, 'image_url' => 'img/Matcha Latte.JPG']);
        Menu::create(['namaMenu' => 'Chai Latte', 'kategori' => 'Beverage', 'harga' => 32000, 'image_url' => 'img/Chai Latte.JPG']);

        Menu::create(['namaMenu' => 'Americano', 'kategori' => 'Coffee', 'harga' => 24000, 'image_url' => 'img/Americano.JPG']);
        Menu::create(['namaMenu' => 'Brazil Santos', 'kategori' => 'Coffee', 'harga' => 24000, 'image_url' => 'img/Brazil Santos.JPG']);
        Menu::create(['namaMenu' => 'Espresso (Single)', 'kategori' => 'Coffee', 'harga' => 18000, 'image_url' => 'img/Espresso.JPG']);
        Menu::create(['namaMenu' => 'Espresso (Double)', 'kategori' => 'Coffee', 'harga' => 20000, 'image_url' => 'img/Espresso2.JPG']);
        Menu::create(['namaMenu' => 'Flat White', 'kategori' => 'Coffee', 'harga' => 32000, 'image_url' => 'img/Flat White.JPG']);
        Menu::create(['namaMenu' => 'Cappucino', 'kategori' => 'Coffee', 'harga' => 28000, 'image_url' => 'img/Cappuccino.JPG']);
        Menu::create(['namaMenu' => 'Vanilla Bean Latte', 'kategori' => 'Coffee', 'harga' => 28000, 'image_url' => 'img/Vanilla Bean Latte.JPG']);
        Menu::create(['namaMenu' => 'Magic Latte', 'kategori' => 'Coffee', 'harga' => 32000, 'image_url' => 'img/Magic Latte.JPG']);
        Menu::create(['namaMenu' => 'Nutty Latte', 'kategori' => 'Coffee', 'harga' => 32000, 'image_url' => 'img/Nutty Latte.JPG']);
        Menu::create(['namaMenu' => 'Mont Blanc', 'kategori' => 'Coffee', 'harga' => 32000, 'image_url' => 'img/Mont Blanc.JPG']);

        Menu::create(['namaMenu' => 'Espresso Tonic', 'kategori' => 'Mocktails', 'harga' => 42000, 'image_url' => 'img/Espresso Tonic.JPG']);
        Menu::create(['namaMenu' => 'Seafoam', 'kategori' => 'Mocktails', 'harga' => 42000, 'image_url' => 'img/Seafoam.JPG']);
        Menu::create(['namaMenu' => 'Zero Foreale', 'kategori' => 'Mocktails', 'harga' => 42000, 'image_url' => 'img/Zero Foreale.JPG']);
        Menu::create(['namaMenu' => 'Grapefruit Lemon Bitter', 'kategori' => 'Mocktails', 'harga' => 38000, 'image_url' => 'img/Grape Fruit.JPG']);
        Menu::create(['namaMenu' => 'Zero Sangria', 'kategori' => 'Mocktails', 'harga' => 42000, 'image_url' => 'img/Zero Sangria.JPG']);
    }
}