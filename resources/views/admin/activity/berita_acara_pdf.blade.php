<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara - {{ $activity['TIKET'] }}</title>
    <style>
        @page {
            margin: 2.5cm 2cm 2cm 2.5cm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }
        .judul {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0;
        }
        .subjudul {
            text-align: center;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .isi {
            text-align: justify;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            padding-top: 50px;
        }
        .ttd-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 10px auto 0 auto;
            font-weight: bold;
        }
        .foto img {
            max-width: 200px;
            margin: 10px;
            border: 1px solid #000;
        }
        .footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 20px;
        text-align: right;
        font-size: 11px;
        margin: 0 30px;
    }
    </style>
</head>
<body>

    <!-- Header -->
    <table style="width: 100%; border: none; margin-bottom: 5px;">
        <tr>
            <td style="width: 25%;">
                <img src="{{ public_path('layouts/assets/images/cmnp.png') }}" alt="Logo" style="width: 120px; height: auto;">
            </td>
            <td style="text-align: center; font-size: 11px;">
                <strong>PT CITRA MARGA NUSAPHALA PERSADA Tbk</strong><br>
                Alamat: Jl. Yos Sudarso Kavling No.28 3, RT.3/RW.11, Sunter Jaya, Kec. Tj. Priok, Jkt Utara, DKI Jakarta 14350<br>
                Telepon: (021) 65306930 | Email: cmnp@citra.co.id
            </td>
        </tr>
    </table>
    <hr style="border: 0; border-top: 1.5px solid #000; margin: 5px 0 20px 0;">

    <!-- Judul -->
    <div class="judul">BERITA ACARA SERAH TERIMA PEKERJAAN</div>
    <div class="subjudul">No: {{ $activity['TIKET'] ?? '...' }}</div>

    <!-- Isi -->
    <p class="isi">
        Pada hari ini {{ \Carbon\Carbon::parse($activity['TGL_ACTIVITY'])->translatedFormat('l, d F Y') }}, yang bertandatangan di bawah ini:
    </p>

    <p class="isi">
        Nama: <strong>{{ session('admin_sap') ?? 'Nama Admin' }}</strong><br>
        Jabatan: <strong>Senior Programmer</strong><br>
        Bertindak untuk dan atas nama <strong>PT CITRA MARGA NUSAPHALA PERSADA Tbk</strong>, yang selanjutnya disebut sebagai <strong>“PIHAK PERTAMA”</strong>.
    </p>

    <p class="isi">
        Nama: <strong>{{ $activity['NM_USER'] }}</strong><br>
        Jabatan: <strong>{{ $activity['JABATAN_USER'] ?? '...' }}</strong><br>
        Bertindak untuk dan atas nama <strong>{{ $activity['NM_COMPANY'] }}</strong>, yang selanjutnya disebut sebagai <strong>“PIHAK KEDUA”</strong>.
    </p>

    <p class="isi">
        PIHAK PERTAMA dan PIHAK KEDUA secara bersama-sama disebut <strong>“Para Pihak”</strong>. Maka dengan ini Para Pihak melakukan pekerjaan dengan lingkup pekerjaan yang sudah disepakati.
    </p>

    <p class="isi">
        PIHAK KEDUA melakukan pekerjaan dengan status pekerjaannya sebagai berikut:
    </p>
    <ol class="isi">
        <li>Menganalisa & Cek Issue SAP HANA</li>
        <li>Melakukan Perbaikan Issue: {{ $activity['SUBJECT'] }}</li>
        <li>Melakukan Testing dengan User</li>
        <li>Memastikan Issue SAP HANA Sudah Terselesaikan dan Berjalan Dengan Baik</li>
    </ol>

    <p class="isi">
        Demikian Berita Acara ini dibuat untuk dipergunakan sebagaimana mestinya.
    </p>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td>
                PIHAK PERTAMA<br>
                <strong>PT CITRA MARGA NUSAPHALA PERSADA Tbk</strong><br><br><br><br>
                <div class="ttd-line">{{ session('admin_sap') ?? 'Nama Admin' }}</div>
            </td>
            <td>
                PIHAK KEDUA<br>
                <strong>PT {{ $activity['NM_COMPANY'] }}</strong><br><br><br><br>
                <div class="ttd-line">{{ $activity['NM_USER'] }}</div>
            </td>
        </tr>
    </table>
    <br><br><br><br><br><br><br><br>

    <!-- Dokumentasi Foto -->
    @if (!empty($fotos))
        <hr>
        <p class="judul">DOKUMENTASI PEKERJAAN</p>
        <div class="foto" style="text-align: center;">
            @foreach ($fotos as $foto)
                @php
                    $fotoPath = public_path('storage/uploads/' . $foto['NM_ACTIVITY_FOTO']);
                    $fotoBase64 = file_exists($fotoPath)
                        ? 'data:image/' . pathinfo($fotoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($fotoPath))
                        : null;
                @endphp
                @if ($fotoBase64)
                    <img src="{{ $fotoBase64 }}" alt="Foto Dokumentasi">
                @endif
            @endforeach
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Dicetak otomatis pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d F Y H:i:s') }} WIB
    </div>

</body>
</html>
