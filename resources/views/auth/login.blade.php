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
        <h2>Login SAP HANA</h2>

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>



            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    {{ $errors->first() }}
                </div>
            @endif
        </form>
    </div>

    <script>
        function toggleFields() {
            const type = document.getElementById('type').value;
            const userFields = document.getElementById('user-fields');
            userFields.style.display = (type === 'user') ? 'block' : 'none';
        }

        window.onload = toggleFields;
    </script>
</body>
</html>
