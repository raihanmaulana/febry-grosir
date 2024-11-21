<?php

namespace App\Exports;

use App\Models\Penjualan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PenjualanExport implements FromCollection, WithHeadings
{
    // Mengambil data dari model Penjualan beserta relasi
    public function collection()
    {
        return Penjualan::with('user')->get()->map(function ($penjualan) {
            return [
                'ID Penjualan' => $penjualan->id_penjualan,
                'Total Item' => $penjualan->total_item,
                'Total Harga' => $penjualan->total_harga,
                'Diskon Persen' => $penjualan->diskon_persen,
                'Diskon Rupiah' => $penjualan->diskon_rupiah,
                'Bayar' => $penjualan->bayar,
                'Kasir' => $penjualan->user->name ?? '',
                'Tanggal' => tanggal_indonesia($penjualan->created_at, false),
            ];
        });
    }

    // Menambahkan header di Excel
    public function headings(): array
    {
        return [
            'ID Penjualan',
            'Total Item',
            'Total Harga',
            'Diskon Persen',
            'Diskon Rupiah',
            'Bayar',
            'Kasir',
            'Tanggal'
        ];
    }
}
