<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Modal untuk memilih periode tanggal -->
<div class="modal fade" id="export-pdf-modal" tabindex="-1" role="dialog" aria-labelledby="exportPdfModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportPdfModalLabel">Export PDF Periode</h5>
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
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inisialisasi flatpickr untuk input tanggal
        flatpickr("#start-date", {
            dateFormat: "Y-m-d", // Format tanggal
        });
        flatpickr("#end-date", {
            dateFormat: "Y-m-d", // Format tanggal
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
    });
</script>