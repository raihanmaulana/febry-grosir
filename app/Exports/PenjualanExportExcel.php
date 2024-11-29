<?php

namespace App\Exports;

use App\Models\Penjualan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenjualanExportExcel implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private $rowNumber = 0;

    public function collection()
    {
        return Penjualan::with(['user', 'penjualanDetail.produk'])->orderBy('id_penjualan', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',           // Nomor urut
            'Tanggal',      // Tanggal transaksi
            'Nama Barang',  // Barang yang dibeli dan jumlahnya
            'Total Item',   // Total item
            'Diskon (%)',   // Diskon persen
            'Diskon (Rp)',  // Diskon rupiah
            'Total Bayar',  // Total pembayaran
            'Kasir',        // Nama kasir
        ];
    }

    public function map($penjualan): array
    {
        $this->rowNumber++;

        $nama_barang = $penjualan->penjualanDetail->map(function ($detail) {
            $nama_produk = $detail->produk->nama_produk ?? 'Tidak Diketahui';
            $jumlah = $detail->jumlah;
            return "{$nama_produk} ({$jumlah})";
        })->implode(', ');

        $diskon_persen_total = $penjualan->diskon_persen_total ?? 0;
        $diskon_rupiah_total = $penjualan->diskon_rupiah_total ?? 0;

        return [
            $this->rowNumber,
            tanggal_indonesia($penjualan->created_at),
            $nama_barang,
            $penjualan->total_item,
            $diskon_persen_total . '%',
            format_uang_excel($diskon_rupiah_total),
            format_uang_excel($penjualan->bayar),
            $penjualan->user->name ?? '-',
        ];
    }


    public function styles(Worksheet $sheet)
    {
        return [
            // Header row (1st row)
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF4CAF50']], // Green color
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
            // Apply borders to all cells
            'A1:H1000' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin',
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => 'center', // Center horizontally
                    'vertical' => 'center',   // Center vertically
                ],
            ],
        ];
    }
}
