<div class="modal fade" id="modal-produk" tabindex="-1" role="dialog" aria-labelledby="modal-produk">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Pilih Produk</h4>
            </div>
            <div class="modal-body">
                <!-- Wrapper for responsive table -->
                <div class="table-responsive">
                    <!-- Add the DataTable class for pagination, search, and sorting functionality -->
                    <table class="table table-striped table-bordered table-produk" id="produk-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Harga Jual</th>
                                <th>Harga Grosir</th>
                                <th>Stok</th>
                                <th><i class="fa fa-cog"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($produk as $key => $item)
                            <tr>
                                <td width="5%">{{ $key+1 }}</td>
                                <td><span class="label label-success">{{ $item->kode_produk }}</span></td>
                                <td>{{ $item->nama_produk }}</td>
                                <td>{{ format_uang($item->harga_jual) }}</td>
                                <td>
    @php
    // Periksa apakah harga_grosir adalah string JSON dan decode jika perlu
    $hargaGrosirValue = 0; // Default value jika tidak ada harga

    // Jika harga_grosir adalah string, coba decode JSON
    if (is_string($item->harga_grosir)) {
        $hargaGrosir = json_decode($item->harga_grosir, true);
        // Jika decode berhasil dan menjadi array, ambil nilai harga
        if (json_last_error() === JSON_ERROR_NONE && is_array($hargaGrosir)) {
            $hargaGrosirValue = $hargaGrosir['harga'] ?? 0;
        }
    } elseif (is_array($item->harga_grosir)) {
        // Jika sudah array, langsung ambil nilai harga dari array
        $hargaGrosirValue = $item->harga_grosir['harga'] ?? 0;
    }

    @endphp
    {{ format_uang($hargaGrosirValue) }} <!-- Tampilkan harga grosir dalam format uang -->
</td>

                                <td>{{ $item->stok }}</td>
                                <td>
                                    <a href="#" class="btn btn-primary btn-xs btn-flat"
                                        onclick="pilihProduk('{{ $item->id_produk }}', '{{ $item->kode_produk }}')">
                                        <i class="fa fa-check-circle"></i> Pilih
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> <!-- End of table-responsive -->
            </div>
        </div>
    </div>
</div>

<!-- Include DataTables JS for pagination, search, etc. -->
<script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#produk-table').DataTable({
            "paging": true, // Enable pagination
            "searching": true, // Enable search box
            "lengthChange": true, // Allow changing the number of rows per page
            "responsive": true, // Make it responsive for mobile view
            "autoWidth": false // Prevent automatic width calculation (useful in some cases)
        });
    });
</script>