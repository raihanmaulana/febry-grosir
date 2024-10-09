<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Detail Member</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .member-detail {
            margin: 50px auto;
            max-width: 600px;
            text-align: center;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }

        .member-detail h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .member-detail p {
            font-size: 18px;
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="member-detail">
        <h1>Detail Member</h1>
        <p><strong>Nama:</strong> {{ $member->nama }}</p>
        <p><strong>Kode Member:</strong> {{ $member->kode_member }}</p>
        <p><strong>Telepon:</strong> {{ $member->telepon }}</p>
        <!-- Tambahkan informasi lain sesuai kebutuhan -->
    </div>
</body>

</html>