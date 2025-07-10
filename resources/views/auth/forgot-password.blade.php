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
        <h2>Lupa Password</h2>
        <style>
    .alert {
        padding: 12px 20px;
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
    }
    .alert-success {
    padding: 12px 20px;
        background-color: #20972a;
        color: #ffffff;
        border: 1px solid #20972a;
        border-radius: 4px;
}
</style>
@if ($errors->has('invalid'))
    <div id="errorMessage" class="alert alert-danger" style="margin-top: 15px;">
        {{ $errors->first('invalid') }}
    </div>

    <script>
        // Hilangkan setelah 3 detik
        setTimeout(() => {
            const msg = document.getElementById('errorMessage');
            if (msg) {
                msg.style.transition = 'opacity 0.5s ease';
                msg.style.opacity = 0;
                setTimeout(() => msg.remove(), 500);
            }
        }, 3000);
    </script>
@endif

@if (session('success'))
    <div id="successMessage" class=" alert-success" style="margin-top: 15px;">
        {{ session('success') }}
    </div>

    <script>
        // Hilangkan setelah 3 detik
        setTimeout(() => {
            const msg = document.getElementById('successMessage');
            if (msg) {
                msg.style.transition = 'opacity 0.5s ease';
                msg.style.opacity = 0;
                setTimeout(() => msg.remove(), 500);
            }
        }, 3000);
    </script>
@endif


        <form method="POST" action="/forgot-password">
            @csrf
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
        <a href="/" >Kembali</a>
    </div>
</body>
</html>
