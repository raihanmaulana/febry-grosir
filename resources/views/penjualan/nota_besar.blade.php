<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nota PDF</title>

    <style>
        /* Styling umum */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        table td {
            padding: 8px;
            vertical-align: top;
        }

        /* Logo dan header */
        .logo-container {
            text-align: center;
            padding: 10px;
        }

        .logo-container img {
            width: 120px;
            margin-bottom: 10px;
        }

        .header-table td {
            padding: 5px 10px;
        }

        /* Tabel data produk */
        .data {
            width: 100%;
            border-collapse: collapse;
        }

        .data th,
        .data td {
            border: 1px solid #ddd;
            text-align: center;
            padding: 8px;
        }

        .data th {
            background-color: #f4f4f4;
        }

        .data td {
            font-size: 12px;
        }

        .text-right {
            text-align: right;
        }

        /* Footer */
        .footer-table td {
            padding: 5px 10px;
        }

        /* Terimakasih & Kasir */
        .thank-you {
            font-weight: bold;
            text-align: left;
            padding: 10px;
        }

        .kasir-info {
            text-align: center;
            padding: 10px;
        }

        .kasir-info b {
            display: block;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <!-- Logo & Alamat Perusahaan -->
    <div class="logo-container">
        <img src="{{ public_path($setting->path_logo) }}" alt="{{ $setting->path_logo }}">
        <p>{{ $setting->nama_perusahaan }}</p>
    </div>

    <!-- Tanggal Nota -->
    <table class="header-table">
        <tr>
            <td>Tanggal</td>
            <td>: {{ tanggal_indonesia(date('Y-m-d')) }}</td>
        </tr>
        <tr>
            <td>Kasir:</td>
            <td>: {{ auth()->user()->name }}</td>
        </tr>
        </div>
    </table>

    <!-- Tabel Data Produk -->
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detail as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item->produk->kode_produk }}</td>
                <td>{{ $item->produk->nama_produk }}</td>
                <td class="text-right">{{ format_uang($item->harga_jual) }}</td>
                <td class="text-right">{{ format_uang($item->jumlah) }}</td>
                <td class="text-right">{{ format_uang($item->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total, Bayar, Diterima, Kembali -->
    <table class="footer-table">
        <tr>
            <td colspan="5" class="text-right"><b>Total Harga</b></td>
            <td class="text-right"><b>{{ format_uang($penjualan->total_harga) }}</b></td>
        </tr>
        <tr>
            <td colspan="5" class="text-right"><b>Total Bayar</b></td>
            <td class="text-right"><b>{{ format_uang($penjualan->bayar) }}</b></td>
        </tr>
        <tr>
            <td colspan="5" class="text-right"><b>Diterima</b></td>
            <td class="text-right"><b>{{ format_uang($penjualan->diterima) }}</b></td>
        </tr>
        <tr>
            <td colspan="5" class="text-right"><b>Kembali</b></td>
            <td class="text-right"><b>{{ format_uang($penjualan->diterima - $penjualan->bayar) }}</b></td>
        </tr>
    </table>

    <!-- Terima Kasih & Nama Kasir -->
     <div class="thank-you" style="text-align: center;">
        <p>{{ ($setting->keterangan_struk) }}</p>
    </div>
</body>

</html>