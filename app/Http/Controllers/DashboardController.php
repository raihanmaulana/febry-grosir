<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Member;
use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $kategori = Kategori::count();
        $kategoriProduk = Kategori::all()->pluck('nama_kategori', 'id_kategori');
        $produk = Produk::count();
        $supplier = Supplier::count();
        $member = Member::count();

        $tanggal_awal = date('Y-m-01');
        $tanggal_akhir = date('Y-m-d');

        $data_tanggal = array();
        $data_pendapatan = array();

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
            return view('admin.dashboard', compact('kategori', 'produk', 'supplier', 'member', 'tanggal_awal', 'tanggal_akhir', 'data_tanggal', 'data_pendapatan'));
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
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id_kategori' => 'required|exists:kategori,id_kategori',
                'nama_produk' => 'required|string|max:255',
                'merk' => 'nullable|string|max:255',
                'harga_jual' => 'required|numeric|min:0',
                'harga_grosir' => 'nullable|numeric|min:0',
                'stok' => 'required|integer|min:0',
            ]);

            $validatedData['kode_produk'] = 'P' . tambah_nol_didepan(Produk::count() + 1, 6);
            $validatedData['added_by'] = auth()->id();

            // Simpan data ke database
            $produk = Produk::create($validatedData);

            // Kirim respons JSON berisi data produk yang baru dibuat
            return response()->json([
                'message' => 'Data berhasil disimpan',
                'data' => [
                    'DT_RowIndex' => $produk->id, // Ganti dengan ID produk
                    'kode_produk' => $produk->kode_produk,
                    'nama_produk' => $produk->nama_produk,
                    'merk' => $produk->merk,
                    'harga_jual' => $produk->harga_jual,
                    'harga_grosir' => $produk->harga_grosir,
                    'stok' => $produk->stok,
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan'], 500);
        }
    }
}
