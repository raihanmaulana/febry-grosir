<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PenjualanExportPeriodeExcel implements FromCollection, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        // Memproses data dan menyesuaikan format
        $formattedData = collect($this->data)->map(function ($item, $index) {
            return [
                $index + 1,                         // No
                $item['tanggal'],                    // Tanggal
                $item['nama_barang'],                // Nama Barang
                $item['total_item'],                 // Total Item
                $item['diskon_persen'],              // Diskon (%)
                $item['diskon_rupiah'],              // Diskon (Rp)
                $item['bayar'],                      // Total Bayar
                $item['kasir'],                      // Kasir
            ];
        });

        return $formattedData;
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

    public function styles($sheet)
    {
        // Styling untuk header
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4CAF50']], // Green color
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Styling untuk semua baris dan kolom data
        $sheet->getStyle('A2:H' . (count($this->data) + 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Menyelaraskan teks di header dan data
        $sheet->getStyle('A1:H' . (count($this->data) + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Mengatur lebar kolom agar tidak menyusut saat dibuka
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']; // Kolom yang ada
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(false); // Menonaktifkan auto sizing
            $sheet->getColumnDimension($column)->setWidth(20); // Tentukan lebar kolom secara manual
        }

        return [];
    }
}
