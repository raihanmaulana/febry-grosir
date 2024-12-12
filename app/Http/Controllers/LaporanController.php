<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Setting;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('laporan.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

    public function getData($awal, $akhir)
    {
        $no = 1;
        $data = array();
        $pendapatan = 0;
        $total_pendapatan = 0;

        while (strtotime($awal) <= strtotime($akhir)) {
            $tanggal = $awal;
            $awal = date('Y-m-d', strtotime("+1 day", strtotime($awal)));

            // Ambil total penjualan untuk tanggal tertentu
            $total_penjualan = Penjualan::where('created_at', 'LIKE', "%$tanggal%")->sum('bayar');

            // Pastikan total_penjualan adalah angka yang benar
            $pendapatan = (float)$total_penjualan; // Ubah menjadi float agar tidak ada masalah format
            $total_pendapatan += $pendapatan;

            $row = array();
            $row['DT_RowIndex'] = $no++;
            $row['tanggal'] = tanggal_indonesia($tanggal, false);

            // Format angka untuk pendapatan
            $row['pendapatan'] = format_uang_pdf($pendapatan);

            $data[] = $row;
        }

        // Menambahkan total pendapatan di akhir
        $data[] = [
            'DT_RowIndex' => '',
            'tanggal' => 'Total',
            'penjualan' => '',
            'pendapatan' => format_uang_pdf($total_pendapatan), // Format total pendapatan
        ];

        return $data;
    }


    public function data($awal, $akhir)
    {
        $data = $this->getData($awal, $akhir);

        return datatables()
            ->of($data)
            ->make(true);
    }

    public function exportPDF($awal, $akhir)
    {
        // Ambil data laporan
        $data = $this->getData($awal, $akhir);

        // Ambil data setting (misalnya, logo, alamat, nama_perusahaan)
        $setting = Setting::first(); // Pastikan Anda memiliki model Setting yang sesuai

        // Buat PDF
        $pdf = PDF::loadView('laporan.pdf', compact('awal', 'akhir', 'data', 'setting'));
        $pdf->setPaper('a4', 'portrait');

        // Stream atau unduh file PDF
        return $pdf->stream('Laporan-pendapatan-' . date('Y-m-d-his') . '.pdf');
    }



    public function exportExcel($awal, $akhir)
{
    // Mengambil data laporan
    $data = $this->getData($awal, $akhir);

    // Ekspor ke Excel
    return Excel::download(new LaporanExport($data), 'Laporan-pendapatan-' . date('Y-m-d-his') . '.xlsx');
}
}
