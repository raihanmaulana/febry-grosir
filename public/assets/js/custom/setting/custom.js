document.addEventListener("DOMContentLoaded", function () {
    // Tangkap semua checkbox dengan class .toggle-status
    const checkboxes = document.querySelectorAll(".toggle-status");

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", function () {
            // Ambil ID setting dari atribut data-id
            let settingId = this.getAttribute("data-id");
            // Tentukan status baru berdasarkan apakah checkbox dicentang atau tidak
            let newStatus = this.checked ? 1 : 0;

            // Kirim AJAX request untuk memperbarui status
            fetch(`/api/settings/update-status/${settingId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                },
                body: JSON.stringify({
                    status: newStatus,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        console.log("Status updated successfully");
                    } else {
                        alert("Error updating status");
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                });
        });
    });

    //  $('[name="apply_all_products"]').on("change", function (e) {
    //      const isChecked = e.target.checked;
    //      $('[name="product_id"]').each(function (e) {
    //          if (isChecked) $(this).val("");
    //      });
    //  });
});

// function confirmDelete(id) {
//     Swal.fire({
//         title: "Are you sure?",
//         text: "You won't be able to revert this!",
//         icon: "warning",
//         showCancelButton: true,
//         confirmButtonColor: "#3085d6",
//         cancelButtonColor: "#d33",
//         confirmButtonText: "Yes, delete it!",
//     }).then((result) => {
//         if (result.isConfirmed) {
//             document.getElementById("delete-form-" + id).submit();
//         }
//     });
// }
$(document).ready(function () {
    // Gunakan event delegation untuk menangani checkbox yang dimuat secara dinamis
    $(document).on("change", 'input[name="status-toggle"]', function () {
        var checkbox = $(this);
        var id = checkbox.data("id");
        var status = checkbox.is(":checked") ? 1 : 0;

        $.ajax({
            url: "/api/settings/update-status/" + id, // Ubah URL sesuai dengan kebutuhan Anda
            method: "POST",
            data: {
                status: status,
                _token: "{{ csrf_token() }}", // Token CSRF untuk keamanan
            },
            success: function (response) {
                console.log("Status updated successfully"); // Berhasil memperbarui status
            },
            error: function (xhr) {
                // Log pesan error detail
                let errorMessage = "An unexpected error occurred";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                // Tampilkan pesan error menggunakan SweetAlert atau alert biasa
                alert(errorMessage); // Ganti dengan Swal.fire jika perlu

                // Pilihan untuk membalikkan status checkbox pada error
                checkbox.prop("checked", !status);
            },
        });
    });
});


$(document).ready(function () {
    $("#search").on("keyup", function () {
        var search = $(this).val().toLowerCase();
        $("#table-settings").each(function () {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(search) === -1) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    });
});

// Memastikan input hanya angka
document.getElementById("fee").addEventListener("keypress", function (e) {
    if (e.key < "0" || e.key > "9") {
        e.preventDefault(); // Mencegah input selain angka
    }
});

// Memformat angka dengan tanda titik sebagai pemisah ribuan
document.getElementById("fee").addEventListener("input", function (e) {
    let value = e.target.value.replace(/\./g, ""); // Menghapus semua titik sebelumnya
    if (!isNaN(value) && value.length > 0) {
        let formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, "."); // Format ribuan dengan titik
        e.target.value = formattedValue;
    }
});
