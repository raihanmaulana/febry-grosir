<?php

namespace Database\Seeders;

use App\Models\Gudang;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GudangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Gudang::create(['nama' => 'Gudang Utama', 'produk' => 'Produk A', 'stok' => 100]);
        Gudang::create(['nama' => 'Gudang Cabang', 'produk' => 'Produk B', 'stok' => 50]);
    }
}
