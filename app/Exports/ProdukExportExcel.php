<?php

namespace App\Exports;

use App\Models\Produk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProdukExportExcel implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private $rowNumber = 0;

    public function collection()
    {
        return Produk::with('kategori')->orderBy('id_produk', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',             // Nomor urut
            'Kode Produk',    // Kode produk
            'Nama Produk',    // Nama produk
            'Nama Kategori',  // Kategori produk
            'Merk',           // Merk produk
            'Harga Beli',     // Harga beli produk
            'Harga Jual',     // Harga jual produk
            'Harga Grosir',   // Harga grosir produk
            'Stok',           // Stok produk
        ];
    }

    public function map($produk): array
    {
        \Log::info('Produk Data: ', $produk->toArray());  // Log produk untuk memeriksa data yang diproses

        return [
            $this->rowNumber,
            $produk->kode_produk,
            $produk->nama_produk,
            $produk->kategori->nama_kategori ?? 'Tidak Diketahui',
            $produk->merk,
            format_uang_excel($produk->harga_beli),
            format_uang_excel($produk->harga_jual),
            format_uang_excel($produk->harga_grosir),
            $produk->stok,
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
            'A1:I1000' => [
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
