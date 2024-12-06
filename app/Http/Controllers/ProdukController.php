<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Exports\ProdukExportExcel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Mengambil kategori dengan nama sebagai value dan ID sebagai key
        $kategori = Kategori::all()->pluck('nama_kategori', 'id_kategori');

        // Urutkan kategori berdasarkan nama_kategori (nilai)
        $kategori = $kategori->sort();

        return view('produk.index', compact('kategori'));
    }


    public function data()
    {

        $produk = Produk::leftJoin('kategori', 'kategori.id_kategori', '=', 'produk.id_kategori')
            ->select('produk.*', 'nama_kategori')
            ->orderByRaw('LEFT(kode_produk, 3)') // Mengurutkan berdasarkan 3 karakter pertama (abjad)
            ->orderByRaw('CAST(SUBSTRING(kode_produk, 4) AS UNSIGNED) ASC') // Mengurutkan berdasarkan angka setelah abjad
            ->get();
        return datatables()
            ->of($produk)
            ->addIndexColumn()
            ->addColumn('select_all', function ($produk) {
                return '
                    <input type="checkbox" name="id_produk[]" value="' . $produk->id_produk . '">
                ';
            })
            ->addColumn('kode_produk', function ($produk) {
                return '<span class="label label-success">' . $produk->kode_produk . '</span>';
            })
            ->addColumn('harga_beli', function ($produk) {
                return format_uang($produk->harga_beli);
            })
            ->addColumn('harga_jual', function ($produk) {
                return format_uang($produk->harga_jual);
            })
            ->addColumn('harga_grosir', function ($produk) {
                return format_uang($produk->harga_grosir);
            })
            ->addColumn('stok', function ($produk) {
                return format_uang($produk->stok);
            })
            ->addColumn('keterangan', function ($produk) {
                return $produk->keterangan;
            })
            ->addColumn('added_by', function ($produk) {
                return $produk->user->name ?? 'Tidak Diketahui';
            })
            ->addColumn('aksi', function ($produk) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`' . route('produk.update', $produk->id_produk) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button type="button" onclick="deleteData(`' . route('produk.destroy', $produk->id_produk) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'kode_produk', 'select_all'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $produk = Produk::find($id);

        return response()->json($produk);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);
        $produk->update($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $produk = Produk::find($id);
        $produk->delete();

        return response(null, 204);
    }

    public function deleteSelected(Request $request)
    {
        foreach ($request->id_produk as $id) {
            $produk = Produk::find($id);
            $produk->delete();
        }

        return response(null, 204);
    }

    public function cetakBarcode(Request $request)
    {
        $dataproduk = array();
        foreach ($request->id_produk as $id) {
            $produk = Produk::find($id);
            $dataproduk[] = $produk;
        }

        $no  = 1;
        $pdf = PDF::loadView('produk.barcode', compact('dataproduk', 'no'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream('produk.pdf');
    }

    public function exportProdukPdf()
    {
        // Ambil data produk dengan relasi kategori
        $produk = Produk::with('kategori')->orderBy('id_produk', 'desc')->get();


        // Mapping data produk
        $data = $produk->map(function ($produk) {
            // Ambil informasi produk
            return [
                'kode_produk' => $produk->kode_produk,
                'nama_produk' => $produk->nama_produk,
                'nama_kategori' => $produk->kategori->nama_kategori ?? 'Tidak Diketahui',
                'merk' => $produk->merk,
                'harga_beli' => format_uang($produk->harga_beli),
                'harga_jual' => format_uang($produk->harga_jual),
                'harga_grosir' => format_uang($produk->harga_grosir),
                'stok' => $produk->stok,
            ];
        });

        // Load view untuk PDF
        $pdf = PDF::loadView('produk.export_pdf', [
            'data' => $data,
        ]);

        // Download PDF
        return $pdf->download('laporan_produk.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ProdukExportExcel, 'laporan_produk.xlsx');
    }
}
