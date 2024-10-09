<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use Illuminate\Http\Request;

class GudangController extends Controller
{
    public function index()
    {
        return view('gudang.index');
    }

    public function data()
    {
        $gudangs = Gudang::orderBy('nama', 'asc')->get();

        return datatables()
            ->of($gudangs)
            ->addIndexColumn()
            ->addColumn('aksi', function ($gudang) {
                return '
                    <button onclick="editForm(`' . route('gudang.update', $gudang->id) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-edit"></i></button>
                    <button onclick="deleteData(`' . route('gudang.destroy', $gudang->id) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        Gudang::create($request->all());
        return response()->json('Data berhasil disimpan', 200);
    }

    public function show($id)
    {
        $gudang = Gudang::findOrFail($id);
        return response()->json($gudang);
    }


    public function update(Request $request, $id)
    {
        $gudang = Gudang::findOrFail($id);
        $gudang->update($request->all());
        return response()->json('Data berhasil diperbarui', 200);
    }

    public function destroy($id)
    {
        Gudang::findOrFail($id)->delete();
        return response()->json('Data berhasil dihapus', 200);
    }
}
