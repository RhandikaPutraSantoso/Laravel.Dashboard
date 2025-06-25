<!DOCTYPE html>
<html>
<head>
    <title>Login - SAP HANA</title>
    <link rel="stylesheet" href="{{ asset('layouts/css/login.css') }}">
    <link rel="shortcut icon" sizes="196x196" href="{{ asset('layouts/assets/images/logo2.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('layouts/assets/images/logo2.png') }}">
    

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    
</head>
<body>
    <div class="login-box">
        <h2>Login SAP HANA</h2>

        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <div class="form-group">
                <label for="type">Login As</label>
                <select name="type" id="type" class="form-control" onchange="toggleFields()">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div id="user-fields" style="display: none;">
                <div class="form-group">
                    <label>Email Perusahaan</label>
                    <input type="email" name="email" class="form-control" placeholder="Email Perusahaan">
                </div>

                <div class="form-group">
                    <label>Nama Perusahaan</label>
                    <input type="text" name="company" class="form-control" placeholder="Nama Perusahaan">
                </div>
            </div>

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
