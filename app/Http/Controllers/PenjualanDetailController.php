<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Setting;
use Illuminate\Http\Request;

class PenjualanDetailController extends Controller
{
    public function index()
    {
        $produk = Produk::orderBy('nama_produk')->get();
        $member = Member::orderBy('nama')->get();
        $diskon_persen = Setting::first()->diskon_persen ?? 0;
        $diskon_rupiah = Setting::first()->diskon_rupiah ?? 0;

        // Cek apakah ada transaksi yang sedang berjalan
        if ($id_penjualan = session('id_penjualan')) {
            $penjualan = Penjualan::find($id_penjualan);
            $memberSelected = $penjualan->member ?? new Member();

            return view('penjualan_detail.index', compact('produk', 'member', 'diskon_persen','diskon_rupiah', 'id_penjualan', 'penjualan', 'memberSelected'));
        } else {
            if (auth()->user()->level == 1) {
                return redirect()->route('transaksi.baru');
            } else {
                return redirect()->route('home');
            }
        }
    }

    public function data($id)
    {
        $detail = PenjualanDetail::with('produk')
        ->where('id_penjualan', $id)
            ->get();

        $data = array();
        $total = 0;
        $total_item = 0;

        foreach ($detail as $item) {
            $row = array();
            $row['kode_produk'] = '<span class="label label-success">' . $item->produk['kode_produk'] . '</span>';
            $row['nama_produk'] = $item->produk['nama_produk'];
            $row['harga_jual']  = 'Rp. ' . format_uang($item->harga_jual);
            $row['harga_grosir']  = 'Rp. ' . format_uang($item->harga_grosir);
            $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="' . $item->id_penjualan_detail . '" value="' . $item->jumlah . '">';
            $row['diskon_persen'] = $item->diskon_persen . '%';
            $row['diskon_rupiah'] = 'Rp. ' . format_uang($item->diskon_rupiah);

            // Hitung subtotal dengan memperhitungkan diskon
            $subtotal = $item->harga_jual * $item->jumlah;
            if ($item->diskon_persen > 0) {
                $subtotal -= ($item->diskon_persen / 100) * $subtotal;
            } elseif ($item->diskon_rupiah > 0) {
                $subtotal -= $item->diskon_rupiah * $item->jumlah;
            }

            $row['subtotal'] = 'Rp. ' . format_uang($subtotal);
            $row['aksi'] = '<div class="btn-group">
                            <button onclick="deleteData(`' . route('transaksi.destroy', $item->id_penjualan_detail) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                        </div>';
            $data[] = $row;

            // Log data setiap item untuk melihat apa yang dikirim
            \Log::info('Data Item:', $row);

            // Total dihitung berdasarkan subtotal setelah diskon
            $total += $subtotal;
            $total_item += $item->jumlah;
        }

        // Log total keseluruhan dan jumlah item
        \Log::info('Total Harga: ' . $total);
        \Log::info('Total Item: ' . $total_item);

        // Tambahkan data total ke dalam tabel
        $data[] = [
            'kode_produk' => '
        <div class="total hide">' . $total . '</div>
        <div class="total_item hide">' . $total_item . '</div>',
            'nama_produk' => '',
            'harga_jual'  => '',
            'harga_grosir'  => '',
            'jumlah'      => '',
            'diskon_persen' => '',
            'diskon_rupiah' => '',
            'subtotal'    => '',
            'aksi'        => '',
        ];

        // Log untuk melihat total dan total item yang dikirim sebagai elemen tersembunyi
        \Log::info('Final Total:', ['total' => $total, 'total_item' => $total_item]);

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['aksi', 'kode_produk', 'jumlah'])
            ->make(true);
    }



    public function store(Request $request)
    {
        $produk = Produk::where('id_produk', $request->id_produk)->first();
        if (! $produk) {
            return response()->json('Data gagal disimpan', 400);
        }

        $detail = new PenjualanDetail();
        $detail->id_penjualan = $request->id_penjualan;
        $detail->id_produk = $produk->id_produk;
        $detail->harga_jual = $produk->harga_jual;
        $detail->harga_grosir = $produk->harga_grosir;
        $detail->jumlah = 1;
        $detail->diskon_persen = $produk->diskon_persen;
        $detail->diskon_rupiah = $produk->diskon_rupiah;
        $detail->subtotal = $produk->harga_jual;
        $detail->save();
        
        return response()->json('Data berhasil disimpan', 200);
    }

    public function update(Request $request, $id)
    {
        $detail = PenjualanDetail::find($id);
        $detail->jumlah = $request->jumlah;
        $detail->subtotal = $detail->harga_jual * $request->jumlah;
        $detail->update();
    }

    public function destroy($id)
    {
        $detail = PenjualanDetail::find($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($diskon = 0, $total = 0, $diterima = 0)
    {
        // Log untuk memeriksa apakah nilai yang dikirim benar
        \Log::info('Total yang diterima di loadForm (sudah didiskon): ' . $total);
        \Log::info('Diterima: ' . $diterima);

        // Pada tahap ini, diskon sudah diterapkan di fungsi data, jadi kita tidak menghitung diskon lagi
        $bayar = $total; // Total yang diterima sudah merupakan total setelah diskon

        // Pastikan nilai tidak menjadi negatif
        if ($bayar < 0) {
            $bayar = 0;
        }

        // Hitung kembalian hanya jika ada pembayaran yang diterima
        $kembali = ($diterima > 0) ? $diterima - $bayar : 0;

        // Siapkan data yang akan dikirim ke view
        return response()->json([
            'totalrp' => format_uang($total),
            'bayarrp' => format_uang($bayar),
            'bayar' => $bayar,
            'terbilang' => ucwords(terbilang($bayar) . ' Rupiah'),
            'kembalirp' => format_uang($kembali),
            'kembali_terbilang' => ucwords(terbilang($kembali) . ' Rupiah'),
        ]);
    }

    public function getProductByCode(Request $request)
    {
        $kodeProduk = $request->kode_produk;
        $produk = Produk::where('kode_produk', $kodeProduk)->first();

        if ($produk) {
            return response()->json($produk);
        }

        return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
    }

}
