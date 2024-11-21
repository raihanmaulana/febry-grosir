<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h3 {
            text-align: center;
            font-size: 16px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .logo-container {
            position: relative;
            width: 100%;
            height: 60px;
            text-align: right;
            margin-bottom: 5px;
        }
        .logo {
            width: 80px;
            height: auto;
            margin-right: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            text-align: center;
            padding: 10px;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: right;
            margin-top: 20px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <!-- Logo di bagian atas kanan -->
    <div class="logo-container">
        @if (isset($setting) && $setting->path_logo)
            <img src="{{ public_path($setting->path_logo) }}" alt="Logo" class="logo">
        @endif
    </div>

    <h3>Laporan Penjualan</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Barang</th> <!-- Kolom tambahan -->
                <th>Total Item</th>
                <!-- <th>Total Harga</th> -->
                <th>Diskon (%)</th>
                <th>Diskon (Rp)</th>
                <th>Total Bayar</th>
                <th>Kasir</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['tanggal'] }}</td>
                    <td>{{ $row['nama_barang'] }}</td> 
                    <td>{{ $row['total_item'] }}</td>
                    <!-- <td>{{ $row['total_harga'] }}</td> -->
                    <td>{{ $row['diskon_persen'] }}</td>
                    <td>{{ $row['diskon_rupiah'] }}</td>
                    <td>{{ $row['bayar'] }}</td>
                    <td>{{ $row['kasir'] }}</td>
                    
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}
    </div>
</body>
</html>
