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
        width: 300px;
        height: auto;
        border: 1px solid #ccc;
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
                <div id="reader" style="width: 300px;"></div>

                <table class="table table-stiped table-bordered table-penjualan">
                    <thead>
                        <th width="5%">No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th width="15%">Jumlah</th>
                        <th>Diskon %</th>
                        <th>Diskon Rupiah</th>
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
                            <input type="hidden" name="id_member" id="id_member" value="{{ $memberSelected->id_member }}">

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
                                <label for="kembali" class="col-lg-2 control-label">Kembali</label>
                                <div class="col-lg-8">
                                    <input type="text" id="kembali" name="kembali" class="form-control" value="0" readonly>
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
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

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
                        data: 'harga_grosir'
                    },
                    {
                        data: 'jumlah'
                    },
                    {
                        data: 'diskon_persen'
                    },
                    {
                        data: 'diskon_rupiah'
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
                paginate: false
            })
            .on('draw.dt', function() {
                loadForm($('#diskon_persen').val(), $('#diskon_rupiah').val());
                setTimeout(() => {
                    $('#diterima').trigger('input');
                }, 300);
            });
        table2 = $('.table-produk').DataTable();

        $(document).on('input', '.quantity', function() {
            let id = $(this).data('id');
            let jumlah = parseInt($(this).val());

            if (jumlah < 1) {
                $(this).val(1);
                alert('Jumlah tidak boleh kurang dari 1');
                return;
            }
            if (jumlah > 10000) {
                $(this).val(10000);
                alert('Jumlah tidak boleh lebih dari 10000');
                return;
            }

            $.post(`{{ url('/transaksi') }}/${id}`, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'put',
                    'jumlah': jumlah
                })
                .done(response => {
                    $(this).on('mouseout', function() {
                        table.ajax.reload(() => loadForm($('#diskon_persen').val(), $('#diskon_rupiah').val()));
                    });
                })
                .fail(errors => {
                    alert('Tidak dapat menyimpan data');
                    return;
                });
        });

        $(document).on('input', '#diskon_persen, #diskon_rupiah', function() {
            if ($(this).val() == "") {
                $(this).val(0).select();
            }

            loadForm($('#diskon_persen').val(), $('#diskon_rupiah').val());
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
    function loadForm(diskonPersen, diskonRupiah, diterima = 0) {
        let total = parseFloat($('#total').val());
        let diskon = 0;
        console.log("Mengirim nilai diterima:", diterima);
        // Jika diskon rupiah ada dan lebih besar dari 0, gunakan diskon rupiah
        if (diskonRupiah > 0) {
            diskon = diskonRupiah;
        }
        // Jika tidak ada diskon rupiah, gunakan diskon persen
        else if (diskonPersen > 0) {
            diskon = (diskonPersen / 100) * total;
        }

        let bayar = total - diskon;

        $('#total_bayar').val(bayar);
        $('#total_bayar_rupiah').text('Rp. ' + bayar.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));

        // Cek apakah jumlah diterima lebih kecil dari total bayar
        let kembalian = diterima - bayar;
        $('#kembalian').val(kembalian);
        $('#kembalian_rupiah').text('Rp. ' + kembalian.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
    }


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

    function loadForm(diskonPersen = 0, diskonRupiah = 0, diterima = 0) {
        let total = $('.total').text();

        // Pastikan log ini menampilkan nilai diterima yang benar
        console.log("Mengirim nilai diterima:", diterima);

        $.get(`{{ url('/transaksi/loadform') }}/${diskonPersen}/${total}/${diterima}`)
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

    $(document).on('input', '#diterima', function() {
        $('#total').val($('.total').text()); // Set nilai total
        $('#total_item').val($('.total_item').text()); // Set nilai total_item
        loadForm($('#diskon_persen').val(), $('#diskon_rupiah').val(), $(this).val());
    });

    // Fungsi untuk memulai proses scan barcode dari kamera
    function scanBarcode() {
        const html5QrCode = new Html5Qrcode("reader");

        // Mendapatkan kamera yang tersedia
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                // Jika ada kamera, pilih kamera pertama
                let cameraId = devices[0].id;

                // Memulai scanner dengan kamera yang dipilih
                html5QrCode.start(
                        cameraId, {
                            fps: 10, // Frame per detik
                            qrbox: {
                                width: 250,
                                height: 250
                            } // Ukuran area scan
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
                            html5QrCode.stop();
                        },
                        (errorMessage) => {
                            // Jika terjadi error, bisa dihandle disini (optional)
                            console.log(`Scanning error: ${errorMessage}`);
                        })
                    .catch(err => {
                        // Handle error jika kamera tidak bisa digunakan
                        console.log(`Camera error: ${err}`);
                    });
            }
        }).catch(err => {
            console.log(`Error mendapatkan kamera: ${err}`);
        });
    }


    const submitForm = document.getElementById("form-penjualan")

    submitForm.addEventListener("submit",  (e)=> {
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
                console.log({response});
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