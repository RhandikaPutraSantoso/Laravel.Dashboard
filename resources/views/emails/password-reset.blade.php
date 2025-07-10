<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Password Baru - SAP Support</title>
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
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        h2 {
            background-color: #005da5;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 20px;
            margin: 0 -30px 20px -30px;
        }
        .label {
            color: #005da5;
            font-weight: bold;
            width: 35%;
            display: inline-block;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #777;
        }
        
    </style>
</head>
<body>
<div class="container">
    
    <h2>Password Baru SAP Support</h2>

    <p>Hai <strong>{{ $username }}</strong>,</p>

    <p>Berikut adalah password baru untuk akun SAP Support Anda:</p>

    <p style="font-size: 18px; color: #000;"><span class="label">Password:</span> {{ $password }}</p>

    <p>Silakan gunakan password ini untuk login ke sistem. Demi keamanan, segera ubah password Anda setelah berhasil masuk.</p>

    <div class="footer">
        Email ini dikirim secara otomatis oleh sistem SAP Support. Harap tidak membalas email ini.
    </div>
</div>
</body>
</html>