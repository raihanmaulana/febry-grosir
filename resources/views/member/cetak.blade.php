<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cetak Kartu Member</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            display: flex;
            flex-direction: column;
            /* Mengatur agar kartu ditampilkan ke bawah */
            align-items: center;
        }

        .card {
            width: 85.60mm;
            height: 53.98mm;
            background: linear-gradient(135deg, #f5a623, #fdbc2c);
            color: #000;
            border-radius: 6px;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            /* Memberi jarak antara setiap kartu */
            position: relative;
        }

        .card-title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
        }

        .info {
            margin-top: 20px;
        }

        .info div {
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .barcode {
            text-align: center;
            margin-top: 10px;
        }

        .barcode img {
            width: 100px;
            height: 100px;
        }

        .logo {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 40px;
            height: 40px;
        }
    </style>
</head>

<body>
    <section class="container">
        @foreach ($datamember as $data)
        @foreach ($data as $item)
        <div class="card">
            <!-- Judul kartu member -->
            <div class="card-title">
                MEMBER CARD
            </div>

            <!-- Info nama dan nomor member -->
            <div class="info">
                <div><strong>NAMA:</strong> {{ $item->nama }}</div>
                <div><strong>NOMOR:</strong> {{ $item->kode_member }}</div>
            </div>

            <!-- Barcode member -->
            <div class="barcode">
                <img src="data:image/png;base64, {{ DNS2D::getBarcodePNG(url('/member/' . $item->kode_member), 'QRCODE') }}" alt="qrcode">
            </div>

            <!-- Logo perusahaan -->
            <img src="{{ public_path($setting->path_logo) }}" alt="Logo" class="logo">
        </div>
        @endforeach
        @endforeach
    </section>
</body>

</html>