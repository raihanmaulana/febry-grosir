<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


<!-- Modal untuk memilih rentang tanggal -->
<div class="modal fade" id="date-filter-modal" tabindex="-1" role="dialog" aria-labelledby="date-filter-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="date-filter-modal-label">Pilih Rentang Tanggal</h5>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <div class="input-group">
                        <input type="text" id="start-date" class="form-control" placeholder="Dari Tanggal" style="width: 150px;">
                        <input type="text" id="end-date" class="form-control" placeholder="Sampai Tanggal" style="width: 150px; margin-left: 10px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="filter-btn">Filter</button>
            </div>
        </div>
    </div>
</div>