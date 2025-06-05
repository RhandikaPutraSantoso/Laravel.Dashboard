<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loading...</title>
    <link rel="stylesheet" href="{{ asset('layouts/css/loading.css') }}">
</head>
<body>
    <div class="loader">
        <span></span><span></span><span></span>
    </div>

    <div class="message">
        Mohon tunggu, sedang masuk ke dashboard...
    </div>

    {{-- Redirect ke dashboard setelah 4 detik --}}
    <script>
        setTimeout(function() {
            window.location.href = "{{ route('admin.dashboardAdmin') }}";

        }, 4000);
    </script>
</body>
</html>
