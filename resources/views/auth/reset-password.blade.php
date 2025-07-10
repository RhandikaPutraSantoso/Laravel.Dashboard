<!DOCTYPE html>
<html>
<head>
    <title>Login - SAP HANA</title>
    <link rel="stylesheet" href="{{ asset('layouts/css/login.css') }}">
    <link rel="shortcut icon" sizes="196x196" href="{{ asset('layouts/assets/images/logo2.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('layouts/assets/images/logo2.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    
</head>
<body>
    <div class="login-box">
<h2>Reset Password</h2>
<form method="POST" action="{{ route('reset-password') }}">
    @csrf
    <input type="hidden" name="id" value="{{ $id }}">
    <input type="hidden" name="login_type" value="{{ $login_type }}">

    <div class="form-group">
    <label>Password baru</label>
    <input type="password" name="password" class="form-control" placeholder="Password Baru" required>
    </div>

    <div class="form-group">
    <label>Ulangi Password</label>
    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi Password" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
</form>
</div>
</body>
</html>

