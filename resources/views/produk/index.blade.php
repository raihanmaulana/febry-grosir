@extends('layouts.master')

@section('title')
Daftar Produk
@endsection

@section('breadcrumb')
@parent
<li class="active">Daftar Produk</li>
@endsection
<link rel="stylesheet" href="{{ asset('css/button.css') }}">
@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <div class="button-container">
                    <button onclick="addForm('{{ route('produk.store') }}')" class="btn btn-success">
                        <i class="fa fa-plus-circle"></i> Tambah
                    </button>
                    <button onclick="deleteSelected('{{ route('produk.delete_selected') }}')" class="btn btn-danger">
                        <i class="fa fa-trash"></i> Hapus
                    </button>
                    <button onclick="cetakBarcode('{{ route('produk.cetak_barcode') }}')" class="btn btn-info">
                        <i class="fa fa-barcode"></i> Cetak Barcode
                    </button>
                    <a href="{{ route('produk.exportExcel') }}" class="btn btn-warning">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </a>
                </div>
            </div>
            <div class="box-body table-responsive">
                <form action="" method="post" class="form-produk">
                    @csrf
                    <table class="table table-stiped table-bordered">
                        <thead>
                            <th width="5%">
                                <input type="checkbox" name="select_all" id="select_all">
                            </th>
                            <th width="5%">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Merk</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Harga Grosir</th>
                            <th>Stok</th>
                            <th>Keterangan</th>
                            <th>Ditambahkan Oleh</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </thead>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

@includeIf('produk.form')
@endsection

@push('scripts')
<script>
    let table;

    $(function() {
        table = $('.table').DataTable({
            processing: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('produk.data') }}",
            },
            columns: [{
                    data: 'select_all',
                    searchable: false,
                    sortable: false
                },
                {
                    data: 'DT_RowIndex',
                    searchable: false,
                    sortable: false
                },
                {
                    data: 'kode_produk'
                },
                {
                    data: 'nama_produk'
                },
                {
                    data: 'nama_kategori'
                },
                {
                    data: 'merk'
                },
                {
                    data: 'harga_beli'
                },
                {
                    data: 'harga_jual'
                },
                {
                    data: 'harga_grosir'
                },
                {
                    data: 'stok'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'added_by'
                },
                {
                    data: 'aksi',
                    searchable: false,
                    sortable: false
                },
            ]
        });

        $('#modal-form').validator().on('submit', function(e) {
            if (!e.preventDefault()) {
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

        $('[name=select_all]').on('click', function() {
            $(':checkbox').prop('checked', this.checked);
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

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Produk');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama_produk]').focus();

        $.get(url)
            .done((response) => {
                console.log(response);
                $('#modal-form [name=kode_produk]').val(response.kode_produk);
                $('#modal-form [name=nama_produk]').val(response.nama_produk);
                $('#modal-form [name=id_kategori]').val(response.id_kategori);
                $('#modal-form [name=merk]').val(response.merk);
                $('#modal-form [name=harga_beli]').val(response.harga_beli);
                $('#modal-form [name=harga_jual]').val(response.harga_jual);
                $('#modal-form [name=stok]').val(response.stok);
                $('#modal-form [name=keterangan]').val(response.keterangan);

                // Menangani data harga_grosir
                if (response.harga_grosir && typeof response.harga_grosir === 'object') {
                    const jenis = response.harga_grosir.jenis;
                    const harga = response.harga_grosir.harga;

                    // Menetapkan nilai untuk elemen select dan input number
                    $('#harga_grosir_jenis').val(jenis); // Menetapkan jenis harga grosir
                    $('#harga_grosir_harga').val(harga); // Menetapkan harga grosir
                }
            })
            .fail((errors) => {
                alert('Tidak dapat menampilkan data');
                return;
            });

    }


    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload();
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }

    function deleteSelected(url) {
        if ($('input:checked').length > 1) {
            if (confirm('Yakin ingin menghapus data terpilih?')) {
                $.post(url, $('.form-produk').serialize())
                    .done((response) => {
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        alert('Tidak dapat menghapus data');
                        return;
                    });
            }
        } else {
            alert('Pilih data yang akan dihapus');
            return;
        }
    }

    function cetakBarcode(url) {
        if ($('input:checked').length < 1) {
            alert('Pilih data yang akan dicetak');
            return;
        } else if ($('input:checked').length < 3) {
            alert('Pilih minimal 3 data untuk dicetak');
            return;
        } else {
            $('.form-produk')
                .attr('target', '_blank')
                .attr('action', url)
                .submit();
        }
    }
</script>
@endpush