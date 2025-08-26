@include('user.components.css')
@include('user.components.sidebar')


<div class="container mt-5 white">
    <div class=" shadow-sm p-4">
    <h4 class="mb-4">Edit Profil</h4>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="this.parentElement.style.display='none';">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
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

<script>
    // Hilangkan alert sukses otomatis setelah 3 detik
    setTimeout(function() {
        var alert = document.getElementById('success-alert');
        if(alert){
            alert.style.display = 'none';
        }
    }, 3000);
</script>

    <form action="{{ route('user.profile.update') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="USERNAME">Username</label>
            <input type="text" name="USERNAME" class="form-control @error('USERNAME') is-invalid @enderror" 
                   value="{{ old('USERNAME', $user['USERNAME']) }}">
            @error('USERNAME')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="EMAIL">Email</label>
            <input type="email" name="EMAIL" class="form-control @error('EMAIL') is-invalid @enderror" 
                   value="{{ old('EMAIL', $user['EMAIL']) }}">
            @error('EMAIL')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="NM_COMPANY">Nama Perusahaan</label>
            <input type="text" name="NM_COMPANY" class="form-control @error('NM_COMPANY') is-invalid @enderror" 
                value="{{ old('NM_COMPANY', $user['NM_COMPANY']) }}" readonly>
            @error('NM_COMPANY')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="JABATAN">Jabatan</label>
            <input type="text" name="JABATAN" class="form-control @error('JABATAN') is-invalid @enderror" 
                   value="{{ old('JABATAN', $user['JABATAN']) }}">
            @error('JABATAN')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
</div>

@include('user.components.scripts')

@include('user.components.themes')