@if(session('login_type') === 'admin')
    @include('admin.components.css')
    @include('admin.components.sidebar')
    @include('admin.components.scripts')
    @include('admin.components.themes')
@elseif(session('login_type') === 'user')
    @include('user.components.css')
    @include('user.components.sidebar')
    @include('user.components.scripts')
    @include('user.components.themes')
@endif



<div class="container mt-5 white">
    <div class=" shadow-sm p-4">
        <h4 class="mb-4">Ganti Password</h4>

        @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>

    <script>
        // Setelah 3 detik, redirect ke login (otomatis logout)
        setTimeout(function () {
            window.location.href = "{{ route('logout') }}";
        }, 3000);
    </script>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="current_password">Password Lama</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required>
            </div>

            <div class="form-group mt-3">
                <label for="password">Password Baru</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="form-group mt-3">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
