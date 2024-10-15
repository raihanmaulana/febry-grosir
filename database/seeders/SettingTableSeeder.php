<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('setting')->insert([
            'id_setting' => 1,
            'nama_perusahaan' => 'Toko Raihan',
            'alamat' => 'Semarang',
            'telepon' => '08123121212',
            'tipe_nota' => 1, // kecil
            'diskon_persen' => 5,
            'diskon_rupiah' => 0,
            'path_logo' => '/img/logo.png',
            // 'path_kartu_member' => '/img/member.png',
        ]);
    }
}
