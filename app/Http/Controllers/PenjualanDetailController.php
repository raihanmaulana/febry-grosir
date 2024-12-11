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

            return view('penjualan_detail.index', compact('produk', 'member', 'diskon_persen', 'diskon_rupiah', 'id_penjualan', 'penjualan', 'memberSelected'));
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
            $toggleChecked = $item->harga_jual === $item->harga_grosir ? 'checked' : '';
            $row = array();
            $row['kode_produk'] = '<span class="label label-success">' . $item->produk['kode_produk'] . '</span>';
            $row['nama_produk'] = $item->produk['nama_produk'];
            $row['harga_jual']  = 'Rp. ' . format_uang($item->harga_jual);
            $row['harga_jual_asli']  = 'Rp. ' . format_uang($item->harga_jual_asli);
            $row['harga_grosir']  = 'Rp. ' . format_uang($item->harga_grosir);
            $row['toggle_harga'] = '<label class="switch">
                                        <input type="checkbox" class="toggle-harga" data-id="' . $item->id_penjualan_detail . '" ' . $toggleChecked . '>
                                        <span class="slider"></span>
                                    </label>';
            $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="' . $item->id_penjualan_detail . '" value="' . $item->jumlah . '" min="0" max="100">';
            $row['stok'] = $item->produk['stok'];
            // Input diskon persen
            $row['diskon_persen'] = '<input type="number" class="form-control input-sm diskon-persen" data-id="' . $item->id_penjualan_detail . '" value="' . $item->diskon_persen . '" min="0" max="100">';

            // Input diskon rupiah
            $row['diskon_rupiah'] = '<input type="number" class="form-control input-sm diskon-rupiah" data-id="' . $item->id_penjualan_detail . '" value="' . $item->diskon_rupiah . '" min="0">';
            $row['harga_yang_digunakan'] = $item->harga_jual == $item->harga_grosir ? 'Grosir' : 'Jual';
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

            $total += $subtotal;
            $total_item += $item->jumlah;
        }

        $data[] = [
            'kode_produk' => '
        <div class="total hide">' . $total . '</div>
        <div class="total_item hide">' . $total_item . '</div>',
            'nama_produk' => '',
            'harga_jual'  => '',
            'harga_jual_asli'  => '',
            'harga_grosir'  => '',
            'jumlah'      => '',
            'stok' => '',
            'diskon_persen' => '',
            'diskon_rupiah' => '',
            'toggle_harga' => '',
            'subtotal'    => '',
            'aksi'        => '',
        ];


        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['aksi', 'kode_produk', 'jumlah', 'diskon_persen', 'diskon_rupiah', 'toggle_harga'])
            ->make(true);
    }



    public function store(Request $request)
    {
        $request->validate([
            'id_penjualan' => 'required|exists:penjualan,id_penjualan',
            'id_produk' => 'required|exists:produk,id_produk',
        ]);

        $produk = Produk::where('id_produk', $request->id_produk)->first();

        if (!$produk) {
            return response()->json('Produk tidak ditemukan', 404);
        }

        // Cek apakah harga_grosir adalah array atau string
        if (is_string($produk->harga_grosir)) {
            // Jika harga_grosir adalah string, coba decode JSON
            $hargaGrosir = json_decode($produk->harga_grosir, true);

            // Jika decode berhasil dan menjadi array, ambil harga dari JSON
            if (json_last_error() === JSON_ERROR_NONE && is_array($hargaGrosir)) {
                $hargaGrosirValue = $hargaGrosir['harga'] ?? 0;
            } else {
                // Jika tidak JSON yang valid, anggap harga_grosir adalah nilai langsung
                $hargaGrosirValue = $produk->harga_grosir;
            }
        } elseif (is_array($produk->harga_grosir)) {
            // Jika harga_grosir sudah berupa array, langsung ambil nilai harga
            $hargaGrosirValue = $produk->harga_grosir['harga'] ?? 0;
        } else {
            // Jika harga_grosir tidak berformat array atau string, set nilai default
            $hargaGrosirValue = 0;
        }

        // Buat objek PenjualanDetail
        $detail = new PenjualanDetail();
        $detail->id_penjualan = $request->id_penjualan;
        $detail->id_produk = $produk->id_produk;
        $detail->harga_jual = $produk->harga_jual;
        $detail->harga_jual_asli = $produk->harga_jual;
        $detail->harga_grosir = $hargaGrosirValue; // Simpan harga grosir
        $detail->jumlah = 1; // Default jumlah 1
        $detail->diskon_persen = $request->diskon_persen ?? 0; // Diskon persen
        $detail->diskon_rupiah = $request->diskon_rupiah ?? 0; // Diskon rupiah

        // Hitung subtotal
        $subtotal = $produk->harga_jual;
        if ($request->diskon_persen > 0) {
            $subtotal -= ($request->diskon_persen / 100) * $subtotal;
        } elseif ($request->diskon_rupiah > 0) {
            $subtotal -= $request->diskon_rupiah;
        }

        $detail->subtotal = $subtotal;
        $detail->save();

        return response()->json('Data berhasil disimpan', 200);
    }


    public function update(Request $request, $id)
    {
        $detail = PenjualanDetail::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $jumlah = $request->jumlah ?? $detail->jumlah;
        $diskonPersen = $request->diskon_persen ?? $detail->diskon_persen;
        $diskonRupiah = $request->diskon_rupiah ?? $detail->diskon_rupiah;
        $gunakanHargaGrosir = filter_var($request->gunakan_harga_grosir, FILTER_VALIDATE_BOOLEAN);

        // Tentukan harga berdasarkan toggle
        if ($gunakanHargaGrosir) {
            $hargaAwal = $detail->harga_grosir;
        } else {
            $hargaAwal = $detail->harga_jual_asli; // Kembalikan ke harga asli
        }

        // Validasi data harga
        if (!$hargaAwal) {
            return response()->json(['message' => 'Data harga tidak valid'], 400);
        }

        // Hitung subtotal berdasarkan diskon
        $subtotal = $hargaAwal * $jumlah;

        if ($diskonPersen > 0) {
            $diskon = ($diskonPersen / 100) * $subtotal;
        } elseif ($diskonRupiah > 0) {
            $diskon = $diskonRupiah * $jumlah;
        } else {
            $diskon = 0;
        }

        $subtotal -= $diskon;

        // Simpan perubahan
        $detail->jumlah = $jumlah;
        $detail->diskon_persen = $diskonPersen;
        $detail->diskon_rupiah = $diskonRupiah;
        $detail->subtotal = $subtotal;
        $detail->harga_jual = $hargaAwal; // Perbarui harga saat ini
        $detail->update();

        return response()->json([
            'message' => 'Data berhasil diperbarui',
            'subtotal' => $subtotal,
            'harga_digunakan' => $gunakanHargaGrosir ? 'Harga Grosir' : 'Harga Jual'
        ], 200);
    }




    public function destroy($id)
    {
        $detail = PenjualanDetail::find($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($diskon = 0, $total = 0, $diterima = 0)
    {
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
