<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Aktivitas SAP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            max-width: 700px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        h2 {
            background-color: #005da5;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 20px;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        td {
            padding: 8px 12px;
            vertical-align: top;
        }
        td.label {
            font-weight: bold;
            width: 30%;
            color: #005da5;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #777;
        }
        ul {
            margin-top: 10px;
            padding-left: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Laporan Aktivitas SAP</h2>

    <table>
        <tr>
            <td class="label">Tiket</td>
            <td>: {{ $activity['TIKET'] }}</td>
        </tr>
        <tr>
            <td class="label">Perusahaan Pemohon</td>
            <td>: {{ $activity['NM_COMPANY'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td>: {{ $activity['MAIL_COMPANY'] }}</td>
        </tr>
        <tr>
            <td class="label">User</td>
            <td>: {{ $activity['NM_USER'] }}</td>
        </tr>
        <tr>
            <td class="label">Subjek</td>
            <td>: {{ $activity['SUBJECT'] }}</td>
        </tr>
        <tr>
            <td class="label">Deskripsi</td>
            <td>: {!! nl2br(e($activity['DESKRIPSI'])) !!}</td>
        </tr>
        <tr>
            <td class="label">Penjelasan Admin</td>
            <td>: {!! nl2br(e($activity['DESKRIPSI_ADMIN'] ?? '-')) !!}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td>: {{ $activity['TGL_ACTIVITY'] }}</td>
        </tr>
    </table>

    <h3 style="margin-top: 25px;">Foto Terkait:</h3>
    @if(count($fotos) > 0)
        <ul>
            @foreach ($fotos as $foto)
                <li>{{ $foto['NM_ACTIVITY_FOTO'] }}</li>
            @endforeach
        </ul>
        <p><em>Semua file telah dilampirkan ke email ini.</em></p>
    @else
        <p><em>Tidak ada foto terlampir.</em></p>
    @endif

    <div class="footer">
        Email ini dikirim secara otomatis oleh sistem SAP Aktivitas.
    </div>
</div>
</body>
</html>
