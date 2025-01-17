<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Setting;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\PenjualanDetail;
use App\Exports\PenjualanExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PenjualanExportExcel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Exports\PenjualanExportPeriodeExcel;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('penjualan.index');
    }

    public function data(Request $request)
    {
        // Ambil parameter tanggal mulai dan tanggal akhir dari request
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        // Query dasar untuk mengambil data penjualan
        $penjualan = Penjualan::orderBy('created_at', 'desc');

        // Jika ada filter tanggal, tambahkan kondisi pada query
        if ($start_date && $end_date) {
            // Ubah format tanggal agar mencakup waktu
            $start_date = $start_date . ' 00:00:00'; // Mulai dari jam 00:00:00
            $end_date = $end_date . ' 23:59:59'; // Sampai jam 23:59:59

            // Terapkan filter tanggal
            $penjualan = $penjualan->whereBetween('created_at', [$start_date, $end_date]);
        }

        $penjualan = $penjualan->get();

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('total_item', function ($penjualan) {
                return format_uang($penjualan->total_item);
            })
            ->addColumn('total_harga', function ($penjualan) {
                return 'Rp. ' . format_uang($penjualan->total_harga);
            })
            ->addColumn('bayar', function ($penjualan) {
                return 'Rp. ' . format_uang($penjualan->bayar);
            })
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->created_at, false);
            })
            ->editColumn('diskon_persen_total', function ($penjualan) {
                return $penjualan->diskon_persen_total . '%';
            })
            ->editColumn('diskon_rupiah_total', function ($penjualan) {
                return 'Rp. ' . $penjualan->diskon_rupiah_total;
            })
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('keterangan', function ($penjualan) {
                return $penjualan->keterangan ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                return '
            <div class="btn-group">
                <button onclick="showDetail(`' . route('penjualan.show', $penjualan->id_penjualan) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                <button onclick="deleteData(`' . route('penjualan.destroy', $penjualan->id_penjualan) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                <button onclick="cetakStruk(`' . route('transaksi.nota_kecil.penjualan', $penjualan->id_penjualan) . '`)" class="btn btn-xs btn-warning btn-flat"><i class="fa fa-print"></i> Cetak Struk</button>
            </div>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }


    public function create()
    {
        $penjualan = new Penjualan();
        // $penjualan->id_member = null;
        $penjualan->total_item = 0;
        $penjualan->total_harga = 0;
        $penjualan->diskon_persen_total = 0;
        $penjualan->diskon_rupiah_total = 0;
        $penjualan->bayar = 0;
        $penjualan->diterima = 0;
        $penjualan->id_user = auth()->id();
        $penjualan->nama_customer = null;
        $penjualan->keterangan = null;
        $penjualan->save();

        session(['id_penjualan' => $penjualan->id_penjualan]);
        return redirect()->route('transaksi.index');
    }

    public function store(Request $request)
    {
        // Temukan transaksi penjualan berdasarkan ID
        $penjualan = Penjualan::findOrFail($request->id_penjualan);

        // Cek apakah ada produk yang sudah ditambahkan ke dalam detail transaksi
        $detail = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();

        // Jika tidak ada detail produk, berikan peringatan tanpa simpan
        if ($detail->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada produk yang ditambahkan dalam transaksi.'
            ], 400);
        }

        // Hitung total harga setelah diskon (jika ada)
        $total_harga = $request->total;

        // Jika diskon persen ada
        if ($request->has('diskon_total_persen') && $request->diskon_total_persen > 0) {
            $total_harga -= ($request->diskon_total_persen / 100) * $request->total;
        }

        // Jika diskon rupiah ada
        if ($request->has('diskon_total_rupiah') && $request->diskon_total_rupiah > 0) {
            $total_harga -= $request->diskon_total_rupiah;
        }

        // Cek apakah jumlah diterima cukup
        if ($request->diterima < $total_harga) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah yang diterima tidak cukup untuk membayar total.'
            ], 400);
        }

        // Mengatur tanggal transaksi, jika tidak ada input created_at, gunakan sekarang
        $createdAt = $request->input('created_at', Carbon::now()->format('Y-m-d H:i:s'));

        // Pastikan created_at dalam format yang benar
        $penjualan->created_at = Carbon::parse($createdAt);

        // Lanjutkan ke update data penjualan jika validasi lulus
        $penjualan->id_member = $request->id_member;
        $penjualan->total_item = $request->total_item;
        $penjualan->total_harga = $total_harga;
        $penjualan->diskon_persen_total = $request->diskon_persen_total;
        $penjualan->diskon_rupiah_total = $request->diskon_rupiah_total;
        $penjualan->bayar = $total_harga;
        $penjualan->diterima = $request->diterima;
        $penjualan->nama_customer = $request->nama_customer;
        $penjualan->keterangan = $request->keterangan;
        $penjualan->update();

        // Update detail penjualan dan stok produk
        foreach ($detail as $item) {
            // Ambil harga awal dari harga jual atau grosir
            $hargaAwal = $item->harga_jual;

            // Hitung subtotal berdasarkan diskon (diskon persen dan rupiah sudah dihitung sebelumnya)
            $subtotal = $hargaAwal * $item->jumlah;

            // Perbarui data detail
            $item->subtotal = $subtotal;
            $item->update();

            // Kurangi stok produk
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok -= $item->jumlah;
                $produk->update();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil disimpan.',
            'redirect' => route('transaksi.selesai')
        ]);
    }



    public function show($id)
    {
        $detail = PenjualanDetail::with('produk')->where('id_penjualan', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('kode_produk', function ($detail) {
                return '<span class="label label-success">' . $detail->produk->kode_produk . '</span>';
            })
            ->addColumn('nama_produk', function ($detail) {
                return $detail->produk->nama_produk;
            })
            ->addColumn('harga_jual', function ($detail) {
                return 'Rp. ' . format_uang($detail->harga_jual);
            })
            ->addColumn('harga_grosir', function ($detail) {
                return 'Rp. ' . format_uang($detail->harga_grosir);
            })
            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })
            ->addColumn('subtotal', function ($detail) {
                return 'Rp. ' . format_uang($detail->subtotal);
            })
            ->rawColumns(['kode_produk'])
            ->make(true);
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        $detail    = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->update();
            }

            $item->delete();
        }

        $penjualan->delete();

        return response(null, 204);
    }

    public function selesai()
    {
        $setting = Setting::first();

        return view('penjualan.selesai', compact('setting'));
    }

    public function notaKecil()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

        $nama_customer = $penjualan->nama_customer;

        return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail', 'nama_customer'));
    }

    public function notaKecilPenjualan($id_penjualan)
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find($id_penjualan);

        if (! $penjualan) {
            abort(404); // Penjualan tidak ditemukan
        }

        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', $id_penjualan)
            ->get();

        $nama_customer = $penjualan->nama_customer;

        return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail', 'nama_customer'));
    }


    public function notaBesar()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

        $pdf = PDF::loadView('penjualan.nota_besar', compact('setting', 'penjualan', 'detail'));
        $pdf->setPaper(0, 0, 609, 440, 'potrait');
        return $pdf->stream('Transaksi-' . date('Y-m-d-his') . '.pdf');
    }

    public function exportPdf()
    {
        // Ambil data penjualan dengan relasi penjualan_detail dan produk
        $penjualan = Penjualan::with(['user', 'penjualanDetail.produk'])->orderBy('id_penjualan', 'desc')->get();

        // Ambil data setting untuk logo
        $setting = Setting::first();

        // Mapping data penjualan
        $data = $penjualan->map(function ($penjualan) {
            // Ambil nama produk dan jumlah dari penjualan_detail
            $nama_barang = $penjualan->penjualanDetail->map(function ($detail) {
                $nama_produk = $detail->produk->nama_produk ?? 'Tidak Diketahui';
                $jumlah = $detail->jumlah; // Ambil jumlah barang yang dibeli
                return "{$nama_produk} ({$jumlah})"; // Format Nama Barang (Jumlah)
            })->implode(', '); // Gabungkan dengan koma

            // Hitung diskon total dari penjualan_detail
            $diskon_persen_total = $penjualan->diskon_persen_total ?? 0;
            $diskon_rupiah_total = $penjualan->diskon_rupiah_total ?? 0;

            return [
                'id' => $penjualan->id_penjualan,
                'tanggal' => tanggal_indonesia($penjualan->created_at, false),
                'total_item' => format_uang($penjualan->total_item),
                'total_harga' => format_uang($penjualan->total_harga),
                'diskon_persen_total' => $diskon_persen_total . '%', // Diskon persen dari penjualan_detail
                'diskon_rupiah_total' => format_uang($diskon_rupiah_total), // Diskon rupiah dari penjualan_detail
                'bayar' => format_uang($penjualan->bayar),
                'kasir' => $penjualan->user->name ?? '-',
                'nama_barang' => $nama_barang, // Nama barang dengan jumlah
            ];
        });

        // Load view untuk PDF
        $pdf = PDF::loadView('penjualan.export_pdf', [
            'data' => $data,
            'setting' => $setting
        ]);

        // Download PDF
        return $pdf->download('laporan_penjualan.pdf');
    }

    public function exportPdfPeriode(Request $request)
    {
        // Ambil parameter tanggal mulai dan tanggal akhir dari request
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        // Validasi jika parameter tanggal tidak ditemukan
        if (!$start_date || !$end_date) {
            abort(400, 'Tanggal mulai dan tanggal akhir diperlukan.');
        }

        // Query untuk mengambil data penjualan dengan relasi penjualan_detail dan produk
        $penjualan = Penjualan::with(['user', 'penjualanDetail.produk'])->orderBy('id_penjualan', 'desc');

        // Terapkan filter tanggal jika ada
        $penjualan = $penjualan->whereBetween('created_at', [
            $start_date . ' 00:00:00',  // Jam mulai
            $end_date . ' 23:59:59'     // Jam akhir
        ]);

        // Ambil data penjualan yang sudah difilter
        $penjualan = $penjualan->get();

        // Mapping data penjualan
        $data = $penjualan->map(function ($penjualan) {
            // Ambil nama produk dan jumlah dari penjualan_detail
            $nama_barang = $penjualan->penjualanDetail->map(function ($detail) {
                // Pastikan produk ada dan nama produk ditemukan
                $nama_produk = $detail->produk->nama_produk ?? 'Tidak Diketahui';
                $jumlah = $detail->jumlah; // Ambil jumlah barang yang dibeli
                return "{$nama_produk} ({$jumlah})"; // Format Nama Barang (Jumlah)
            })->implode(', '); // Gabungkan dengan koma

            // Menghitung diskon total dari penjualan_detail (jika ada)
            $diskon_persen_total = $penjualan->diskon_persen_total ?? 0;
            $diskon_rupiah_total = $penjualan->diskon_rupiah_total ?? 0;

            // Pastikan semua data yang diperlukan ada
            return [
                'id' => $penjualan->id_penjualan,
                'tanggal' => tanggal_indonesia($penjualan->created_at, false),
                'total_item' => format_uang($penjualan->total_item),
                'total_harga' => format_uang($penjualan->total_harga),
                'diskon_persen_total' => $diskon_persen_total . '%', // Diskon persen
                'diskon_rupiah_total' => format_uang($diskon_rupiah_total), // Diskon rupiah
                'bayar' => format_uang($penjualan->bayar),
                'kasir' => $penjualan->user->name ?? '-',
                'nama_barang' => $nama_barang, // Nama barang dengan jumlah
            ];
        });

        // Ambil data setting untuk logo (jika ada)
        $setting = Setting::first();

        // Load view untuk PDF
        $pdf = PDF::loadView('penjualan.export_pdf', [
            'data' => $data,
            'setting' => $setting // Jika ada setting untuk logo atau informasi lain
        ]);

        // Download PDF
        return $pdf->download('laporan_penjualan_periode.pdf');
    }


    public function exportExcel()
    {
        return Excel::download(new PenjualanExportExcel, 'laporan_penjualan.xlsx');
    }

    public function exportExcelPeriode(Request $request)
    {
        // Ambil parameter tanggal mulai dan tanggal akhir dari request
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        // Validasi jika parameter tanggal tidak ditemukan
        if (!$start_date || !$end_date) {
            abort(400, 'Tanggal mulai dan tanggal akhir diperlukan.');
        }

        // Query untuk mengambil data penjualan dengan relasi penjualan_detail dan produk
        $penjualan = Penjualan::with(['user', 'penjualanDetail.produk'])->orderBy('id_penjualan', 'desc');

        // Terapkan filter tanggal jika ada
        $penjualan = $penjualan->whereBetween('created_at', [
            $start_date . ' 00:00:00',  // Jam mulai
            $end_date . ' 23:59:59'     // Jam akhir
        ]);

        // Ambil data penjualan yang sudah difilter
        $penjualan = $penjualan->get();

        // Mapping data penjualan
        $data = $penjualan->map(function ($penjualan) {
            $nama_barang = $penjualan->penjualanDetail->map(function ($detail) {
                $nama_produk = $detail->produk->nama_produk ?? 'Tidak Diketahui';
                $jumlah = $detail->jumlah;
                return "{$nama_produk} ({$jumlah})";
            })->implode(', ');

            $diskon_persen_total = $penjualan->diskon_persen_total ?? 0;
            $diskon_rupiah_total = $penjualan->diskon_rupiah_total ?? 0;

            return [
                'no' => $penjualan->id_penjualan, // Ganti 'ID Penjualan' dengan 'no'
                'tanggal' => tanggal_indonesia($penjualan->created_at, false), // Pastikan key 'tanggal'
                'total_item' => format_uang_excel($penjualan->total_item),
                'total_harga' => format_uang_excel($penjualan->total_harga),
                'diskon_persen' => $diskon_persen_total . '%',
                'diskon_rupiah' => format_uang_excel($diskon_rupiah_total),
                'bayar' => format_uang_excel($penjualan->bayar),
                'kasir' => $penjualan->user->name ?? '-',
                'nama_barang' => $nama_barang, // Pastikan key 'nama_barang'
            ];
        });

        // Kirim data ke Excel
        return Excel::download(new PenjualanExportPeriodeExcel($data), 'laporan_penjualan_periode.xlsx');
    }


    public function getTotalPenjualan(Request $request)
    {
        $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->startOfDay();
        $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date)->endOfDay();

        // Hitung total penjualan dalam rentang waktu yang diberikan
        $totalPenjualan = Penjualan::whereBetween('tanggal', [$startDate, $endDate])
            ->sum('bayar'); // Gantilah 'bayar' dengan nama kolom yang sesuai di database Anda

        return response()->json([
            'total' => number_format($totalPenjualan, 0, ',', '.')
        ]);
    }
}
