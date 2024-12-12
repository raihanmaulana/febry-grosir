@extends('layouts.master')

@section('title')
Daftar Penjualan
@endsection

@section('breadcrumb')
@parent
<li class="active">Daftar Penjualan</li>
@endsection

@section('content')

<link rel="stylesheet" href="{{ asset('css/button.css') }}">
<!-- Tambahkan Flatpickr CSS -->
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <div class="button-container">
                    <a href="{{ route('penjualan.exportPdf') }}" class="btn btn-success">
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                    </a>
                    <a href="{{ route('penjualan.exportExcel') }}" class="btn btn-warning">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </a>
                    <button onclick="openDateFilterModal()" class="btn btn-info">
                        <i class="fa fa-calendar"></i> Filter Periode
                    </button>
                    <button type="button" id="open-export-modal" class="btn btn-primary">
                        <i class="fa fa-calendar"></i> Export Periode
                    </button>
                </div>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered table-penjualan">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal</th>
                            <th>Total Item</th>
                            <th>Total Harga</th>
                            <th>Diskon %</th>
                            <th>Diskon Rupiah</th>
                            <th>Total Bayar</th>
                            <th>Kasir</th>
                            <th>Keterangan</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal Export -->
<div class="modal fade" id="export-modal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Data Periode</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="start-date">Tanggal Mulai</label>
                    <input type="text" id="start-date" class="form-control" placeholder="Pilih Tanggal Mulai">
                </div>
                <div class="form-group">
                    <label for="end-date">Tanggal Akhir</label>
                    <input type="text" id="end-date" class="form-control" placeholder="Pilih Tanggal Akhir">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" id="export-pdf-btn" class="btn btn-primary">Export PDF</button>
                <button type="button" id="export-excel-btn" class="btn btn-success">Export Excel</button>
            </div>
        </div>
    </div>
</div>


@includeIf('penjualan.detail')
@includeIf('penjualan.form')
@endsection

@push('scripts')
<script>
    let table, table1;

    $(function() {
        var table = $('.table-penjualan').DataTable({
            processing: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('penjualan.data') }}",
                data: function(d) {
                    // Ambil nilai tanggal mulai dan tanggal akhir
                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    searchable: false,
                    sortable: false
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'total_item'
                },
                {
                    data: 'total_harga'
                },
                {
                    data: 'diskon_persen_total'
                },
                {
                    data: 'diskon_rupiah_total'
                },
                {
                    data: 'bayar'
                },
                {
                    data: 'kasir'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'aksi',
                    searchable: false,
                    sortable: false
                }
            ]
        });

        // Event listener untuk tombol filter
        $('#filter-btn').on('click', function() {
            table.ajax.reload();

            $('#date-filter-modal').modal('hide');
        });

        table1 = $('.table-detail').DataTable({
            processing: true,
            bSort: false,
            dom: 'Brt',
            columns: [{
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
                    data: 'harga_jual'
                },
                {
                    data: 'jumlah'
                },
                {
                    data: 'subtotal'
                },
            ]
        })
    });



    function showDetail(url) {
        $('#modal-detail').modal('show');

        table1.ajax.url(url);
        table1.ajax.reload();
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

    $(document).ready(function() {
        flatpickr("#start-date", {
            dateFormat: "Y-m-d", // Format tanggal yang digunakan
        });
        flatpickr("#end-date", {
            dateFormat: "Y-m-d", // Format tanggal yang digunakan
        });
    });

    // Fungsi untuk membuka modal
    function openDateFilterModal() {
        $('#date-filter-modal').modal('show');
    }
</script>

<script>
    // Fungsi untuk membuka popup cetak struk
    function cetakStruk(url) {
        popupCenter(url, 'Cetak Struk', 625, 500);
    }

    function popupCenter(url, title, w, h) {
        const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
        const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

        const width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        const systemZoom = width / window.screen.availWidth;
        const left = (width - w) / 2 / systemZoom + dualScreenLeft;
        const top = (height - h) / 2 / systemZoom + dualScreenTop;
        const newWindow = window.open(url, title,
            `
            scrollbars=yes,
            width  = ${w / systemZoom}, 
            height = ${h / systemZoom}, 
            top    = ${top}, 
            left   = ${left}
        `);

        if (window.focus) newWindow.focus();
    }
</script>

<script>
    $(document).ready(function() {
        // Inisialisasi flatpickr untuk input tanggal
        flatpickr("#start-date", {
            dateFormat: "Y-m-d", // Format tanggal
            onChange: function(selectedDates, dateStr, instance) {
                console.log('Start Date:', dateStr);  // Log untuk memeriksa tanggal yang dipilih
            }
        });

        flatpickr("#end-date", {
            dateFormat: "Y-m-d", // Format tanggal
            onChange: function(selectedDates, dateStr, instance) {
                console.log('End Date:', dateStr);  // Log untuk memeriksa tanggal yang dipilih
            }
        });

        // Fungsi untuk membuka modal
        $('#open-export-modal').on('click', function() {
            $('#export-modal').modal('show');
        });

        // Fungsi untuk menangani tombol export PDF
        $('#export-pdf-btn').on('click', function() {
            var start_date = $('#start-date').val();
            var end_date = $('#end-date').val();

            // Validasi agar tanggal mulai dan tanggal akhir tidak kosong
            if (!start_date || !end_date) {
                alert('Silakan pilih periode tanggal terlebih dahulu.');
                return;
            }

            // Buat URL untuk ekspor PDF dengan parameter start_date dan end_date
            var url = "{{ route('penjualan.exportPdfPeriode') }}" + "?start_date=" + start_date + "&end_date=" + end_date;

            // Arahkan browser ke URL ekspor PDF
            window.location.href = url; // Redirect ke URL ekspor PDF
        });

        // Fungsi untuk menangani tombol export Excel
        $('#export-excel-btn').on('click', function() {
            var start_date = $('#start-date').val();
            var end_date = $('#end-date').val();

            // Validasi agar tanggal mulai dan tanggal akhir tidak kosong
            if (!start_date || !end_date) {
                alert('Silakan pilih periode tanggal terlebih dahulu.');
                return;
            }

            // Buat URL untuk ekspor Excel dengan parameter start_date dan end_date
            var url = "{{ route('penjualan.exportExcelPeriode') }}" + "?start_date=" + start_date + "&end_date=" + end_date;

            // Arahkan browser ke URL ekspor Excel
            window.location.href = url; // Redirect ke URL ekspor Excel
        });
    });
</script>
@endpush