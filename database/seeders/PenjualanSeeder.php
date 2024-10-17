<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Buat penjualan acak untuk bulan Oktober
        for ($i = 0; $i < 30; $i++) {
            DB::table('penjualan')->insert([
                'id_member' => rand(1, 50), // Anggap ada 50 member
                'total_item' => rand(1, 20), // Anggap 1-20 item per transaksi
                'total_harga' => rand(50000, 500000), // Harga total antara 50.000 - 500.000
                'diskon_persen' => rand(0, 30), // Diskon acak antara 0% hingga 30%
                'diskon_rupiah' => rand(0, 50000), // Diskon acak dalam rupiah, maksimal 50.000
                'bayar' => rand(50000, 500000), // Bayar dalam range total harga
                'diterima' => rand(50000, 500000), // Diterima antara 50.000 hingga 500.000
                'id_user' => rand(1, 10), // Anggap ada 10 user yang menangani transaksi
                'created_at' => Carbon::create(2024, 10, rand(1, 31), rand(0, 23), rand(0, 59), rand(0, 59)), // Waktu acak di bulan Oktober 2023
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
