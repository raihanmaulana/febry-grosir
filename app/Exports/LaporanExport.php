<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LaporanExport implements FromCollection, WithHeadings, WithStyles, WithColumnFormatting
{
    protected $data;
    protected $totalPendapatan;

    public function __construct($data)
    {
        $this->data = $data;

        // Hitung total pendapatan, konversi ke float untuk format angka
        $this->totalPendapatan = array_sum(array_map(function ($item) {
            return (int)str_replace('.', '', $item['pendapatan']); // Ubah ke integer sebelum dijumlahkan
        }, $data));
    }

    public function collection()
    {
        $data = collect($this->data);

        // Format pendapatan menggunakan format_uang_excel
        $formattedData = $data->map(function ($item, $index) {
            return [
                'No' => $index + 1,
                'tanggal' => $item['tanggal'],
                'pendapatan' => format_uang_excel((int)str_replace('.', '', $item['pendapatan'])),
            ];
        });

        // Tambahkan total hanya jika belum ada
        if (!$formattedData->contains('tanggal', 'Total')) {
            $formattedData->push([
                'No' => '',
                'tanggal' => 'Total',
                'pendapatan' => format_uang_excel($this->totalPendapatan),
            ]);
        }

        return $formattedData;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Pendapatan',
        ];
    }

    public function styles($sheet)
    {
        // Header styling
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Tanggal styling
        $lastRow = $sheet->getHighestRow(); // Dapatkan baris terakhir
        $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Penebalan untuk total pendapatan
        $sheet->getStyle("C{$lastRow}")->getFont()->setBold(true);

        return [];
    }

    public function columnFormats(): array
    {
        return [
            // Kolom Pendapatan tidak perlu format angka lagi karena sudah diformat sebagai string
            'C' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
