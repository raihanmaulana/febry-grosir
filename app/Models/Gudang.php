<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    use HasFactory;
    // Tentukan tabel yang digunakan (jika nama tabel tidak jamak)
    protected $table = 'gudang';

    // Tentukan kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'nama',
        'produk',
        'stok',
    ];
}
