@extends('layouts.master')

@section('title')
Dashboard
@endsection

@section('breadcrumb')
@parent
<li class="active">Dashboard</li>
@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body text-center">
                <h1>Selamat Datang</h1>
                <h2>Anda login sebagai KASIR</h2>
                <br><br>
                <div class="text-center">
                    <div>
                        <a href="{{ route('transaksi.baru') }}" class="btn btn-success btn-lg mb-2">Transaksi Baru</a>
                    </div>
                    <div>
                        <button
                            onclick="addForm('{{ route('produk.kasir.store') }}')"
                            class="btn btn-success btn-lg">
                            <i class="fa fa-plus-circle"></i> Tambah Produk
                        </button>
                    </div>
                </div>

                <style>
                    .btn {
                        width: auto;
                        /* Tombol hanya selebar konten */
                        padding: 10px 30px;
                        /* Ukuran proporsional */
                    }

                    .mb-2 {
                        margin-bottom: 1rem;
                        /* Tambahkan jarak bawah untuk tombol pertama */
                    }

                    .text-center {
                        text-align: center;
                        /* Pusatkan konten */
                    }
                </style>

                <br><br><br>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Produk Yang Anda Tambahkan</h3>
            </div>
            <div class="box-body">
                <form action="" method="post" class="form-produk">
                    @csrf
                    <table class="table table-stiped table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th>Merk</th>
                                <th>Harga Jual</th>
                                <th>Harga Grosir</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Tambahkan jQuery terlebih dahulu -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">

<script src="{{ asset('AdminLTE-2/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('AdminLTE-2/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>



@includeIf('produk.form_kasir')
<script>
    let table;

$(function () {
    table = $('.table').DataTable({
        processing: true,
        autoWidth: false,
        ajax: {
            url: '{{ route('produk.kasir.data') }}',
        },
        columns: [
            {data: 'DT_RowIndex', searchable: false, sortable: false},
            {data: 'kode_produk'},
            {data: 'nama_produk'},
            {data: 'merk'},
            {data: 'harga_jual'},
            {data: 'harga_grosir'},
            {data: 'stok'},
        ]
    });

    $('#modal-form').validator().on('submit', function (e) {
        if (! e.preventDefault()) {
            $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                .done((response) => {
                    $('#modal-form').modal('hide');
                    table.ajax.reload();
                })
                .fail((errors) => {
                    alert('Tidak dapat menyimpan data');
                    return;
                });
        }
    });
});



    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Produk');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_produk]').focus();
    }
</script>

<!-- /.row (main row) -->
@endsection