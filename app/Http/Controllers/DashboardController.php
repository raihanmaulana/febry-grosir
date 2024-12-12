<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
{
    $kategori = Kategori::count();
    $kategoriProduk = Kategori::all()->pluck('nama_kategori', 'id_kategori');
    $produk = Produk::count();
    $penjualan = Penjualan::count();
    $member = Member::count();

    $tanggal_awal = date('Y-m-01');
    $tanggal_akhir = date('Y-m-d');

    $data_tanggal = array();
    $data_pendapatan = array();

    // Total Penjualan Hari Ini
    $total_penjualan_hari_ini = Penjualan::whereDate('created_at', today())->sum('bayar');

    while (strtotime($tanggal_awal) <= strtotime($tanggal_akhir)) {
        $data_tanggal[] = (int) substr($tanggal_awal, 8, 2);

        $total_penjualan = Penjualan::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
        $total_pembelian = Pembelian::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
        $total_pengeluaran = Pengeluaran::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('nominal');

        $pendapatan = $total_penjualan - $total_pembelian - $total_pengeluaran;
        $data_pendapatan[] += $pendapatan;

        $tanggal_awal = date('Y-m-d', strtotime("+1 day", strtotime($tanggal_awal)));
    }

    if (auth()->user()->level == 1) {
        return view('admin.dashboard', compact('kategori', 'produk', 'penjualan', 'member', 'tanggal_awal', 'tanggal_akhir', 'data_tanggal', 'data_pendapatan', 'total_penjualan_hari_ini'));
    } else {
        return view('kasir.dashboard', compact('kategoriProduk'));
    }
}


    public function dataKasir()
    {
        $produk = Produk::where('added_by', auth()->id())->get();

        return datatables()
            ->of($produk)
            ->addIndexColumn()
            ->addColumn('harga_grosir', function ($produk) {
                // Periksa apakah harga_grosir adalah array atau string JSON
                $hargaGrosir = '';

                // Jika harga_grosir adalah string, coba decode JSON
                if (is_string($produk->harga_grosir)) {
                    $hargaGrosir = json_decode($produk->harga_grosir, true);
                } else {
                    // Jika harga_grosir sudah berupa array
                    $hargaGrosir = $produk->harga_grosir;
                }

                // Jika hasil decode atau harga_grosir sudah array, ambil harga dan jenisnya
                if (is_array($hargaGrosir)) {
                    // Ambil harga dan jenis dari array
                    $harga = $hargaGrosir['harga'] ?? 0;  // Ambil harga, jika tidak ada set 0
                    $jenis = $hargaGrosir['jenis'] ?? '';  // Ambil jenis, jika tidak ada set kosong
                } else {
                    // Jika tidak ada data harga, set default
                    $harga = 0;
                    $jenis = '';
                }

                // Tentukan jenis harga grosir
                $jenisHarga = '';
                if ($jenis == 'lusin') {
                    $jenisHarga = 'Lusin';
                } elseif ($jenis == 'setengah_lusin') {
                    $jenisHarga = 'Setengah Lusin';
                } elseif ($jenis == 'pcs') {
                    $jenisHarga = 'Pcs';
                }

                // Format output: "harga/jenis"
                $hargaFormat = format_uang($harga); // Pastikan fungsi format_uang() sudah ada
                return "{$hargaFormat}/{$jenisHarga}"; // Gabungkan harga dan jenis
            })
            ->rawColumns(['harga_grosir'])  // Pastikan kolom harga grosir bisa diproses sebagai HTML
            ->make(true);
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Data pengguna yang ditambahkan
            $request['added_by'] = Auth::id();

            // Simpan produk, validasi akan dilakukan di model
            $produk = Produk::create($request->all());

            DB::commit();

            return response()->json([
                'message' => 'Data berhasil disimpan',
                'kode_produk' => $produk->kode_produk,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
