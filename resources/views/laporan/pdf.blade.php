<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Pendapatan</title>

    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
        }

        .header img {
            width: 100px;
            height: auto;
        }

        .header .company-name {
            text-align: center;
            flex-grow: 1;
        }

        .header .address {
            text-align: right;
        }

        .header h2,
        .header p {
            margin: 0;
        }

        h3,
        h4 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            text-align: center;
            padding: 8px;
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

        .hr-line {
            border-top: 2px solid #000;
            margin: 20px 30px;
        }
    </style>
</head>

<body>

    <!-- Header Section: Logo, Company Name, and Address -->
    <div class="header">
        <!-- Logo Perusahaan di kiri -->

        <!-- Nama Perusahaan di tengah -->
        <div class="company-name">
            <h2>{{ $setting->nama_perusahaan }}</h2>
        </div>

        <!-- Alamat Perusahaan di kanan -->
        <div class="address">
            <p>{{ $setting->alamat }}</p>
        </div>
    </div>

    <!-- Garis pemisah -->
    <div class="hr-line"></div>

    <h3>Laporan Pendapatan</h3>
    <h4>
        Tanggal {{ tanggal_indonesia($awal, false) }}
        s/d
        Tanggal {{ tanggal_indonesia($akhir, false) }}
    </h4>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Tanggal</th>
                <th>Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['tanggal'] }}</td>
                <td>
                    {{ $row['pendapatan'] == 0 ? '0' : $row['pendapatan'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer Section -->
    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>
    </div>

</body>

</html>