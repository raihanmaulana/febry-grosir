<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Setting;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use App\Models\PenjualanDetail;
use App\Exports\PenjualanExport;
use App\Exports\PenjualanExportExcel;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('penjualan.index');
    }

    public function data()
    {
        $penjualan = Penjualan::orderBy('id_penjualan', 'desc')->get();
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
            ->editColumn('diskon_persen', function ($penjualan) {
                return $penjualan->diskon_persen . '%';
            })
            ->editColumn('diskon_rupiah', function ($penjualan) {
                return 'Rp. ' . $penjualan->diskon_rupiah;
            })
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail(`' . route('penjualan.show', $penjualan->id_penjualan) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                    <button onclick="deleteData(`' . route('penjualan.destroy', $penjualan->id_penjualan) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
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
        $penjualan->diskon_persen = 0;
        $penjualan->diskon_rupiah = 0;
        $penjualan->bayar = 0;
        $penjualan->diterima = 0;
        $penjualan->id_user = auth()->id();
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
        if ($request->has('diskon_rupiah') && $request->diskon_rupiah > 0) {
            $total_harga = $request->total - $request->diskon_rupiah;
        } elseif ($request->has('diskon_persen') && $request->diskon_persen > 0) {
            $total_harga = $request->total - ($request->diskon_persen / 100 * $request->total);
        }

        // Cek apakah jumlah diterima cukup
        if ($request->diterima < $total_harga) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah yang diterima tidak cukup untuk membayar total.'
            ], 400);
        }

        // Lanjutkan ke update data penjualan jika validasi lulus
        $penjualan->id_member = $request->id_member;
        $penjualan->total_item = $request->total_item;
        $penjualan->total_harga = $total_harga;
        $penjualan->bayar = $total_harga;
        $penjualan->diterima = $request->diterima;
        $penjualan->update();

        // Update detail penjualan dan stok produk
        foreach ($detail as $item) {
            // Ambil harga awal dari harga jual atau grosir
            $hargaAwal = $item->harga_jual;

            // Tentukan diskon yang diterapkan
            $diskonPersen = $item->diskon_persen;
            $diskonRupiah = $item->diskon_rupiah;

            // Hitung subtotal berdasarkan diskon
            $subtotal = $hargaAwal * $item->jumlah;
            if ($diskonPersen > 0) {
                $subtotal -= ($diskonPersen / 100) * $subtotal;
            } elseif ($diskonRupiah > 0) {
                $subtotal -= $diskonRupiah * $item->jumlah;
            }

            // Perbarui data detail
            $item->diskon_persen = $diskonPersen;
            $item->diskon_rupiah = $diskonRupiah;
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

        return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail'));
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
            $diskon_persen_total = $penjualan->penjualanDetail->sum('diskon_persen');
            $diskon_rupiah_total = $penjualan->penjualanDetail->sum(function ($detail) {
                return $detail->diskon_rupiah * $detail->jumlah; // Diskon rupiah * jumlah barang
            });

            return [
                'id' => $penjualan->id_penjualan,
                'tanggal' => tanggal_indonesia($penjualan->created_at, false),
                'total_item' => format_uang($penjualan->total_item),
                'total_harga' => format_uang($penjualan->total_harga),
                'diskon_persen' => $diskon_persen_total . '%', // Diskon persen dari penjualan_detail
                'diskon_rupiah' => format_uang($diskon_rupiah_total), // Diskon rupiah dari penjualan_detail
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

    public function exportExcel()
    {
        return Excel::download(new PenjualanExportExcel, 'laporan_penjualan.xlsx');
    }
}
