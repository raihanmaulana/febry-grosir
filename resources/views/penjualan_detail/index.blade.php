@extends('layouts.master')

@section('title')
Transaksi Penjualan
@endsection

@push('css')
<style>
    .tampil-bayar {
        font-size: 5em;
        text-align: center;
        height: 100px;
    }

    .tampil-terbilang {
        padding: 10px;
        background: #f0f0f0;
    }

    .table-penjualan tbody tr:last-child {
        display: none;
    }

    @media(max-width: 768px) {
        .tampil-bayar {
            font-size: 3em;
            height: 70px;
            padding-top: 5px;
        }
    }

    #reader {
        margin-top: 20px;
        width: 400px;
        height: auto;
        border: 1px solid #ccc;
    }

    .table-container {
        overflow-x: auto;
        /* Enable horizontal scrolling */
        width: 100%;
        /* Pastikan tabel memenuhi lebar kontainer */
    }

    .table-penjualan {
        width: 100%;
        /* Pastikan tabel mengisi kontainer */
        table-layout: auto;
        /* Mengatur lebar kolom secara otomatis */
    }

    .table-penjualan th,
    .table-penjualan td {
        white-space: nowrap;
        /* Mencegah kolom pecah menjadi beberapa baris */
    }

    .switch {
    position: relative;
    display: inline-block;
    width: 40px; /* Lebar toggle yang lebih kecil */
    height: 20px; /* Tinggi toggle yang lebih kecil */
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 20px; /* Sesuaikan agar berbentuk elips */
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px; /* Sesuaikan ukuran tombol */
    width: 16px;  /* Sesuaikan ukuran tombol */
    left: 2px;    /* Jarak antara tombol dan sisi kiri slider */
    bottom: 2px;  /* Jarak antara tombol dan sisi bawah slider */
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #2196F3;
}

input:checked + .slider:before {
    transform: translateX(20px); /* Sesuaikan dengan ukuran toggle */
}

</style>
@endpush

@section('breadcrumb')
@parent
<li class="active">Transaksi Penjaualn</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body">

                <form class="form-produk">
                    @csrf
                    <div class="form-group row">
                        <label for="kode_produk" class="col-lg-2">Kode Produk</label>
                        <div class="col-lg-5">
                            <div class="input-group">
                                <input type="hidden" name="id_penjualan" id="id_penjualan" value="{{ $id_penjualan }}">
                                <input type="hidden" name="id_produk" id="id_produk">
                                <input type="text" class="form-control" name="kode_produk" id="kode_produk" onkeypress="return enterKodeProduk(event)">
                                <span class="input-group-btn">
                                    <button onclick="tampilProduk()" class="btn btn-info btn-flat" type="button"><i class="fa fa-arrow-right"></i></button>
                                    <button onclick="scanBarcode()" class="btn btn-success btn-flat" type="button">Scan Barcode</button>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Div untuk menampilkan preview kamera -->
                <div id="reader" style="width: 335px;"></div>

                <table class="table table-stiped table-bordered table-penjualan">
                    <thead>
                        <th width="5%">No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Harga Jual</th>
                        <th>Harga Grosir</th>
                        <th>Grosir</th>
                        <th width="15%">Jumlah</th>
                        <th width="15%">Stok</th>
                        <th width="10%">Diskon %</th>
                        <th width="10%">Diskon Rupiah</th>
                        <th>Subtotal</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="tampil-bayar bg-primary"></div>
                        <div class="tampil-terbilang"></div>
                    </div>
                    <div class="col-lg-4">
                        <form id="form-penjualan" class="form-penjualan">
                            @csrf
                            <input type="hidden" name="id_penjualan" value="{{ $id_penjualan }}">
                            <input type="hidden" name="total" id="total">
                            <input type="hidden" name="total_item" id="total_item">
                            <input type="hidden" name="bayar" id="bayar">
                            <input type="hidden" name="nama_customer" id="nama_customer">
                            <input type="hidden" name="keterangan" id="keterangan">
                            <input type="hidden" name="diskon_total_persen" id="diskon_total_persen">
                            <input type="hidden" name="diskon_total_rupiah" id="diskon_total_rupiah">


                            <div class="form-group row">
                                <label for="totalrp" class="col-lg-2 control-label">Total</label>
                                <div class="col-lg-8">
                                    <input type="text" id="totalrp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bayar" class="col-lg-2 control-label">Bayar</label>
                                <div class="col-lg-8">
                                    <input type="text" id="bayarrp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="diskon_persen_total" class="col-lg-2 control-label">Diskon (%)</label>
                                <div class="col-lg-8">
                                    <input type="number" id="diskon_persen_total" name="diskon_persen_total" class="form-control" value="{{ old('diskon_persen_total', $penjualan->diskon_persen_total ?? '') }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="diskon_rupiah_total" class="col-lg-2 control-label">Diskon (Rp)</label>
                                <div class="col-lg-8">
                                    <input type="number" id="diskon_rupiah_total" name="diskon_rupiah_total" class="form-control" value="{{ old('diskon_rupiah_total', $penjualan->diskon_rupiah_total ?? '') }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="diterima" class="col-lg-2 control-label">Diterima</label>
                                <div class="col-lg-8">
                                    <input type="number" id="diterima" class="form-control @error('diterima') is-invalid @enderror" name="diterima" value="{{ old('diterima', $penjualan->diterima ?? 0) }}">

                                    @error('diterima')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="nama_customer" class="col-lg-2 control-label">Nama Customer</label>
                                <div class="col-lg-8">
                                    <input type="text" id="nama_customer" name="nama_customer" class="form-control" value="{{ old('nama_customer', $penjualan->nama_customer ?? '') }}">
                                </div>
                            </div>


                            <div class="form-group row">
                                <label for="kembali" class="col-lg-2 control-label">Kembali</label>
                                <div class="col-lg-8">
                                    <input type="text" id="kembali" name="kembali" class="form-control" value="0" readonly>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="keterangan" class="col-lg-2 control-label">Keterangan`</label>
                                <div class="col-lg-8">
                                    <input type="text" id="keterangan" name="keterangan" class="form-control" value="{{ old('keterangan', $penjualan->keterangan ?? '') }}">
                                </div>
                            </div>

                            <div class="form-group row">
    <label for="created_at" class="col-lg-2 control-label">Tanggal Transaksi</label>
    <div class="col-lg-8">
        <input type="datetime-local" id="created_at" name="created_at" class="form-control"
               value="{{ old('created_at', \Carbon\Carbon::now()->format('Y-m-d\TH:i')) }}">
    </div>
</div>


                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary btn-sm btn-flat pull-right btn-simpan"><i class="fa fa-floppy-o"></i> Simpan Transaksi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

@includeIf('penjualan_detail.produk')
@includeIf('penjualan_detail.member')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

<script>
    let table, table2;

    $(function() {
        $('body').addClass('sidebar-collapse');

        table = $('.table-penjualan').DataTable({
                processing: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('transaksi.data', $id_penjualan) }}",
                },
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
                        data: 'harga_jual_asli'
                    },
                    {
                        data: 'harga_grosir'
                    },
                    { data: 'toggle_harga' },
                    {
                        data: 'jumlah'
                    },
                    {
                        data: 'stok'
                    },
                    {
                        data: 'diskon_persen',
                    },
                    {
                        data: 'diskon_rupiah',
                    },
                    {
                        data: 'subtotal'
                    },
                    {
                        data: 'aksi',
                        searchable: false,
                        sortable: false
                    },
                ],
                dom: 'Brt',
                bSort: false,
                paginate: false,
                scrollX: true, // Enable horizontal scroll
                responsive: true, // Optional: allows the table to be more responsive
            })
            .on('draw.dt', function() {
                loadForm($('#diskon_persen').val(), $('#diskon_rupiah').val());
                        setTimeout(() => {
                            $('#diterima').trigger('input');
                        }, 300);
                    });
                table2 = $('.table-produk').DataTable();

                function updateData(id, jumlah, diskonPersen, diskonRupiah, gunakanHargaGrosir) {
                    console.log("Mengirim data: ", { id, jumlah, diskonPersen, diskonRupiah, gunakanHargaGrosir });

                    $.post(`{{ url('/transaksi') }}/${id}`, {
                        '_token': $('[name=csrf-token]').attr('content'),
                        '_method': 'put',
                        'jumlah': jumlah,
                        'diskon_persen': diskonPersen,
                        'diskon_rupiah': diskonRupiah,
                        'gunakan_harga_grosir': gunakanHargaGrosir
                    })
                    .done(response => {
                        console.log("Berhasil diperbarui: ", response);
                        table.ajax.reload(null, false); // Reload tabel tanpa reset paging
                    })
                    .fail(errors => {
                        console.error("Error saat menyimpan data: ", errors);
                        alert('Tidak dapat menyimpan data');
                    });
                }

                $(document).on('input', '.quantity', function() {
                    let id = $(this).data('id');
                    let jumlah = parseInt($(this).val());
                    let diskonPersen = parseFloat($(this).closest('tr').find('.diskon-persen').val()) || 0;
                    let diskonRupiah = parseFloat($(this).closest('tr').find('.diskon-rupiah').val()) || 0;
                    let gunakanHargaGrosir = $(this).closest('tr').find('.toggle-harga').is(':checked');

                    updateData(id, jumlah, diskonPersen, diskonRupiah, gunakanHargaGrosir);
                });

                $(document).on('input', '.diskon-persen', function() {
                    let id = $(this).data('id');
                    let diskonPersen = parseFloat($(this).val()) || 0;

                    // Jika diskon persen diisi (lebih besar dari 0), disable input diskon rupiah
                    let diskonRupiahInput = $(this).closest('tr').find('.diskon-rupiah');
                    if (diskonPersen > 0) {
                        diskonRupiahInput.val(0).prop('disabled', true); // Reset nilai rupiah dan disable
                    } else {
                        diskonRupiahInput.prop('disabled', false); // Aktifkan kembali jika diskon persen kembali 0
                    }

                    let jumlah = parseInt($(this).closest('tr').find('.quantity').val()) || 1;
                    let diskonRupiah = 0; // Karena diskon rupiah disabled, pastikan ini 0
                    let gunakanHargaGrosir = $(this).closest('tr').find('.toggle-harga').is(':checked');

                    updateData(id, jumlah, diskonPersen, diskonRupiah, gunakanHargaGrosir);
                });

                $(document).on('input', '.diskon-rupiah', function() {
                    let id = $(this).data('id');
                    let diskonRupiah = parseFloat($(this).val()) || 0;

                    // Jika diskon rupiah diisi (lebih besar dari 0), disable input diskon persen
                    let diskonPersenInput = $(this).closest('tr').find('.diskon-persen');
                    if (diskonRupiah > 0) {
                        diskonPersenInput.val(0).prop('disabled', true); // Reset nilai persen dan disable
                    } else {
                        diskonPersenInput.prop('disabled', false); // Aktifkan kembali jika diskon rupiah kembali 0
                    }

                    let jumlah = parseInt($(this).closest('tr').find('.quantity').val()) || 1;
                    let diskonPersen = 0; // Karena diskon persen disabled, pastikan ini 0
                    let gunakanHargaGrosir = $(this).closest('tr').find('.toggle-harga').is(':checked');

                    updateData(id, jumlah, diskonPersen, diskonRupiah, gunakanHargaGrosir);
                });

                $(document).on('change', '.toggle-harga', function() {
                    let id = $(this).data('id');
                    let gunakanHargaGrosir = $(this).is(':checked'); // Nilai true jika toggle aktif, false jika mati
                    let jumlah = parseInt($(this).closest('tr').find('.quantity').val()) || 1;
                    let diskonPersen = parseFloat($(this).closest('tr').find('.diskon-persen').val()) || 0;
                    let diskonRupiah = parseFloat($(this).closest('tr').find('.diskon-rupiah').val()) || 0;

                    updateData(id, jumlah, diskonPersen, diskonRupiah, gunakanHargaGrosir);
                });

        $('#diterima').on('input', function() {
            if ($(this).val() == "") {
                $(this).val(0).select();
            }
            console.log("Nilai diterima:", $(this).val()); // Tambahkan log ini
            loadForm($('#diskon_persen').val(), $('#diskon_rupiah').val(), $(this).val());
        }).focus(function() {
            $(this).select();
        });

        // $('.btn-simpan').on('click', function() {
        //     $('.form-penjualan').submit();
        // });
    });

    // Fungsi untuk load form dengan pengkondisian diskon persen atau rupiah
    // function loadForm(diskonPersen, diskonRupiah, diterima = 0) {
    //     let total = parseFloat($('#total').val());
    //     let diskon = 0;
    //     console.log("Mengirim nilai diterima:", diterima);
    //     // Jika diskon rupiah ada dan lebih besar dari 0, gunakan diskon rupiah
    //     if (diskonRupiah > 0) {
    //         diskon = diskonRupiah;
    //     }
    //     // Jika tidak ada diskon rupiah, gunakan diskon persen
    //     else if (diskonPersen > 0) {
    //         diskon = (diskonPersen / 100) * total;
    //     }

    //     let bayar = total - diskon;

    //     $('#total_bayar').val(bayar);
    //     $('#total_bayar_rupiah').text('Rp. ' + bayar.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));

    //     // Cek apakah jumlah diterima lebih kecil dari total bayar
    //     let kembalian = diterima - bayar;
    //     $('#kembalian').val(kembalian);
    //     $('#kembalian_rupiah').text('Rp. ' + kembalian.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
    // }


    function tampilProduk() {
        $('#modal-produk').modal('show');
    }

    function hideProduk() {
        $('#modal-produk').modal('hide');
    }

    function pilihProduk(id, kode) {
        $('#id_produk').val(id);
        $('#kode_produk').val(kode);
        hideProduk();
        tambahProduk();
    }

    function enterKodeProduk(event) {
        if (event.key === "Enter") {
            event.preventDefault(); // Mencegah form submit default

            const kodeProduk = $('#kode_produk').val().trim(); // Ambil kode produk

            // Kirim permintaan untuk mendapatkan data produk
            $.get(`{{ url('/transaksi/get-product-by-code') }}`, {
                    kode_produk: kodeProduk
                })
                .done(response => {
                    // Jika produk ditemukan, masukkan ke transaksi
                    $('#id_produk').val(response.id_produk); // Set ID produk
                    $('#kode_produk').val(response.kode_produk); // Set kode produk
                    // Pastikan produk ditambahkan atau penanganan lain yang diperlukan
                    tambahProduk(); // Panggil fungsi untuk menambah produk jika perlu
                })
                .fail(error => {
                    alert('Kode produk tidak ditemukan. Silakan periksa kembali kode produk.');
                });
        }
        return false;
    }


    // Fungsi untuk menambah produk berdasarkan form
    function tambahProduk() {
        $.post("{{ route('transaksi.store')}}", $('.form-produk').serialize())
            .done(response => {
                $('#kode_produk').val(''); // Kosongkan input kode produk setelah submit berhasil
                $('#kode_produk').focus(); // Kembalikan fokus ke input
                table.ajax.reload(() => loadForm($('#diskon').val())); // Reload table
            })
            .fail(errors => {
                alert('Tidak dapat menyimpan data');
                return;
            });
    }


    function tampilMember() {
        $('#modal-member').modal('show');
    }

    function pilihMember(id, kode) {
        $('#id_member').val(id);
        $('#kode_member').val(kode);

        // Mengambil diskon persentase dan rupiah dari server atau variabel yang relevan
        let diskonPersen = '{{ $diskon_persen }}'; // Misalkan kamu memiliki variabel ini dari server
        let diskonRupiah = '{{ $diskon_rupiah }}'; // Misalkan kamu juga memiliki variabel ini

        // Memilih diskon yang akan diterapkan, jika diskon rupiah lebih dari 0, maka digunakan
        if (diskonRupiah > 0) {
            $('#diskon').val(diskonRupiah);
        } else if (diskonPersen > 0) {
            $('#diskon').val(diskonPersen);
        } else {
            $('#diskon').val(0); // Jika tidak ada diskon
        }

        // Memanggil fungsi loadForm dengan diskon yang dipilih
        loadForm($('#diskon').val());
        $('#diterima').val(0).focus().select();
        hideMember();
    }


    function hideMember() {
        $('#modal-member').modal('hide');
    }

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload(() => loadForm($('#diskon').val()));
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }

    function loadForm(diskonPersen = 0, diskonRupiah = 0, diterima = 0, diskonTotalPersen = 0, diskonTotalRupiah = 0) {
    let total = parseFloat($('.total').text().replace('Rp. ', '').replace(',', '')) || 0; // Total harga sebelum diskon
    let totalItem = parseInt($('.total_item').text()) || 0; // Total item

    // Perhitungan diskon dan total
    let totalDiskonPersen = (diskonTotalPersen / 100) * total;
    let totalDiskonRupiah = diskonTotalRupiah;
    let totalSetelahDiskon = total - totalDiskonPersen - totalDiskonRupiah;

    // Pastikan total dan total_item diperbarui
    $('#total').val(totalSetelahDiskon); // Set total ke input hidden
    $('#total_item').val(totalItem); // Set total_item ke input hidden

    $.get(`{{ url('/transaksi/loadform') }}/${diskonPersen}/${totalSetelahDiskon}/${diterima}`)
        .done(response => {
            $('#totalrp').val('Rp. ' + response.totalrp);
            $('#bayarrp').val('Rp. ' + response.bayarrp);
            $('#bayar').val(response.bayar);
            $('.tampil-bayar').text('Bayar: Rp. ' + response.bayarrp);
            $('.tampil-terbilang').text(response.terbilang);
            $('#kembali').val('Rp.' + response.kembalirp);
            if ($('#diterima').val() != 0) {
                $('.tampil-bayar').text('Kembali: Rp. ' + response.kembalirp);
                $('.tampil-terbilang').text(response.kembali_terbilang);
            }
        })
        .fail(errors => {
            alert('Tidak dapat menampilkan data');
        });
}

$(document).on('input', '#diskon_persen_total', function() {
    let diskonTotalPersen = parseFloat($(this).val()) || 0;
    let diskonTotalRupiah = parseFloat($('#diskon_rupiah_total').val()) || 0;
    let diterima = parseFloat($('#diterima').val()) || 0;

    loadForm($('#diskon_persen').val(), $('#diskon_rupiah').val(), diterima, diskonTotalPersen, diskonTotalRupiah);
});

$(document).on('input', '#diskon_rupiah_total', function() {
    let diskonTotalRupiah = parseFloat($(this).val()) || 0;
    let diskonTotalPersen = parseFloat($('#diskon_persen_total').val()) || 0;
    let diterima = parseFloat($('#diterima').val()) || 0;

    loadForm($('#diskon_persen').val(), $('#diskon_rupiah').val(), diterima, diskonTotalPersen, diskonTotalRupiah);
});

$(document).on('input', '#diterima', function() {
    let diterima = parseFloat($(this).val()) || 0;
    let diskonTotalPersen = parseFloat($('#diskon_persen_total').val()) || 0;
    let diskonTotalRupiah = parseFloat($('#diskon_rupiah_total').val()) || 0;

    // Hitung total setelah diskon dan kirim ke backend
    let total = parseFloat($('.total').text().replace('Rp. ', '').replace(',', '')) || 0;
    let totalDiskonPersen = (diskonTotalPersen / 100) * total;
    let totalDiskonRupiah = diskonTotalRupiah;
    let totalSetelahDiskon = total - totalDiskonPersen - totalDiskonRupiah;

    // Ambil nilai total_item dari elemen yang sesuai di frontend
    let totalItem = parseInt($('.total_item').text()) || 0;  // Pastikan total_item berisi jumlah item yang benar
    
    // Kirim data ke loadForm
    loadForm($('#diskon_persen').val(), $('#diskon_rupiah').val(), diterima, diskonTotalPersen, diskonTotalRupiah, totalSetelahDiskon, totalItem);
});





    let html5QrCode;
    let scanning = false; // Flag untuk mengetahui apakah sedang scan atau tidak
    
    function scanBarcode() {
        // Jika sedang scanning, hentikan dan sembunyikan tampilan scanner
        if (scanning) {
            stopScan();
        } else {
            startScan();
        }
    }
    
    function startScan() {
        scanning = true;
        const readerElement = document.getElementById("reader");
        readerElement.style.display = "block"; // Tampilkan area pemindaian
    
        html5QrCode = new Html5Qrcode("reader");
    
        // Paksa menggunakan kamera belakang dengan facingMode "environment"
        const cameraConfig = { facingMode: "environment" };
    
        html5QrCode
            .start(
                cameraConfig, 
                {
                    fps: 10, // Frame per detik
                    qrbox: { width: 250, height: 250 } // Ukuran area scan
                },
                (decodedText, decodedResult) => {
                    // Jika kode barcode berhasil di-scan
                    $('#kode_produk').val(decodedText); // Isi input kode_produk dengan hasil scan
    
                    // Kirim permintaan ke server untuk mendapatkan data produk
                    $.get(`{{ url('/transaksi/get-product-by-code') }}`, {
                            kode_produk: decodedText // Menggunakan kode produk yang di-scan
                        })
                        .done(response => {
                            // Jika produk ditemukan, masukkan ID produk dan panggil tambahProduk
                            $('#id_produk').val(response.id_produk); // Set ID produk
                            $('#kode_produk').val(response.kode_produk); // Set kode produk
    
                            // Memanggil fungsi tambahProduk setelah produk ditemukan
                            tambahProduk();
                        })
                        .fail(error => {
                            alert('Kode produk tidak ditemukan. Silakan periksa kembali kode produk.');
                        });
    
                    // Hentikan scan setelah barcode ditemukan
                    stopScan();
                },
                (errorMessage) => {
                    // Jika terjadi error, bisa dihandle disini (optional)
                    console.log(`Scanning error: ${errorMessage}`);
                }
            )
            .catch(err => {
                // Handle error jika kamera tidak bisa digunakan
                console.log(`Camera error: ${err}`);
            });
    }
    
    function stopScan() {
        // Hentikan scanner dan sembunyikan area pemindaian
        if (html5QrCode) {
            html5QrCode.stop(); // Berhenti memindai
            scanning = false;
            document.getElementById("reader").style.display = "none"; // Sembunyikan area pemindaian
        }
    }
    
    const submitForm = document.getElementById("form-penjualan")

    submitForm.addEventListener("submit", (e) => {
        e.preventDefault()
        let form = $('#form-penjualan');
        let formData = form.serialize();

        console.log(formData); // Log payload untuk memastikan isi sebelum dikirim

        $.ajax({
            url: "{{ route('transaksi.simpan') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                // Jika berhasil, tampilkan pesan sukses
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                }).then(() => {
                    // Redirect ke halaman selesai jika diperlukan
                    window.location.href = response.redirect;

                });
                console.log({
                    response
                });
            },
            error: function(xhr) {
                console.log(xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan, coba lagi!',
                });
            }
        });
    })
</script>
@endpush