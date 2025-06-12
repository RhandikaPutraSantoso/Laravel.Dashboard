<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara - {{ $activity['TIKET'] }}</title>
    <style>
       <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #000;
        margin: 30px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #000;
        padding: 6px;
        text-align: left;
    }
    .no-border td, .no-border th {
        border: none;
    }
    .header-table td {
        border: none;
        padding: 2px 5px;
    }
    .logo {
        width: 170px;
        height: 100px;
        
    }
    .judul {
        text-align: center;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        margin: 10px 0;
    }
    .foto img {
        max-width: 300px;
        max-height: 200px;
        margin-top: 10px;
        border: 1px solid #000;
    }
    .signature-table td {
        text-align: center;
        padding-top: 50px;
        border: none;
    }
    .ttd-line {
        margin-top: 50px;
        border-top: 1px solid #000;
        width: 80%;
        margin-left: auto;
        margin-right: auto;
        font-weight: bold;
    }
    .small {
        font-size: 12px;
    }
</style>

    </style>
</head>
<body>

    <table class="header-table">
    <tr>
        <td >
            <img src="{{ $logoPath }}" class="logo">
        </td>
        <td style="text-align: center;" >
            {{ $activity['ALAMAT_COMPANY'] }}<br>
            Telp: {{ $activity['TELP_COMPANY'] }} | Email: {{ $activity['MAIL_COMPANY'] }}
        </td>
    </tr>
</table>
<hr>
    

    <div class="judul">Berita Acara Aktivitas</div>

    <!-- Informasi Aktivitas -->
    <table>
        <tr>
            <th>Nomor Tiket</th>
            <td>{{ $activity['TIKET'] }}</td>
            <th>Tanggal</th>
            <td>{{ \Carbon\Carbon::parse($activity['TGL_ACTIVITY'])->format('d F Y') }}</td>
        </tr>
        <tr>
            <th>Perusahaan</th>
            <td>{{ $activity['NM_COMPANY'] }}</td>
            <th>Email</th>
            <td>{{ $activity['MAIL_COMPANY'] }}</td>
        </tr>
        <tr>
            <th>Pengguna</th>
            <td colspan="3">{{ $activity['NM_USER'] }}</td>
            
        </tr>
        <tr>
            <th>Subjek Aktivitas</th>
            <td colspan="3">{{ $activity['SUBJECT'] }}</td>
        </tr>
        <tr>
            <th>Deskripsi</th>
            <td colspan="3">{!! nl2br(e($activity['DESKRIPSI'])) !!}</td>
        </tr>

        @if (!empty($activity['KOMENTAR']))
            <tr>
                <th>Komentar</th>
                <td colspan="3">{!! nl2br(e($activity['KOMENTAR'])) !!}</td>
            </tr>
            <tr>
                <th>Tgl Komentar</th>
                <td colspan="3">{{ \Carbon\Carbon::parse($activity['TGL_KOMENTAR'])->format('d F Y') }}</td>
            </tr>
        @endif
    </table>
    <hr>

    <!-- Foto Dokumentasi -->
@if (!empty($fotos))
    <div class="foto" style="text-align:center;">
        <p><strong>Dokumentasi Visual</strong></p>
        <br>
        @foreach ($fotos as $foto)
            @php
                $fotoPath = public_path('storage/uploads/' . $foto['NM_ACTIVITY_FOTO']);
                $fotoBase64 = file_exists($fotoPath)
                    ? 'data:image/' . pathinfo($fotoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($fotoPath))
                    : null;
            @endphp
            @if ($fotoBase64)
                <img src="{{ $fotoBase64 }}" alt="Foto"
                     style="width: 120px; height: auto; margin: 10px; border: 1px solid #000;">
            @endif
        @endforeach
    </div>
@endif


<hr>
    <!-- Penutup -->
    <p class="small" style="text-align: justify;" >
        Demikian berita acara ini dibuat secara komprehensif, mencakup seluruh detail aktivitas yang telah dilaksanakan, guna memastikan kelengkapan dokumentasi resmi. Keabsahan berita acara ini diperkuat dengan adanya tanda tangan dari para pihak yang terlibat dan berkepentingan, menegaskan akuntabilitas serta validitas informasi yang tercatat.

    </p>
<hr>

    <!-- Tanda Tangan -->
    <table class="signature-table" style="margin-top: 5px;">
        <tr>
            <td>
                Mengetahui,<br>Admin<br><br><br>
                <div class="ttd-line">{{ session('admin_sap') ?? 'Nama Admin' }}</div>
            </td>
            <td>
                Disetujui oleh,<br>Manager<br><br><br>
                <div class="ttd-line">{{ $managerName ?? 'Nama Manager' }}</div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <p class="small" style="text-align: right; margin-top: 40px;">
        Dicetak otomatis pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d F Y H:i:s') }} WIB
    </p>

</body>
</html>
