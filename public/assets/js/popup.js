function confirmDeleteContent(id, deleteUrl) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            // Ambil CSRF token dari meta tag
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            // Membuat dan mengirimkan form penghapusan secara dinamis
            const form = document.createElement("form");
            form.action = deleteUrl;
            form.method = "POST";
            form.style.display = "none";

            const csrfInput = document.createElement("input");
            csrfInput.type = "hidden";
            csrfInput.name = "_token";
            csrfInput.value = csrfToken;

            const methodInput = document.createElement("input");
            methodInput.type = "hidden";
            methodInput.name = "_method";
            methodInput.value = "DELETE";

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);

            form.submit(); // Mengirimkan form secara otomatis
        }
    });
}
