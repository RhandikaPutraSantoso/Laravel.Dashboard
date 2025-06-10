<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Ubah Email - SAP HANA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  @include('admin.components.css')
</head>
<body>
@include('admin.components.sidebar')

<div class="padding">
    <div class="container">
        <h2>Ubah Email</h2>
        <div class="alert alert-warning">
            <strong>Perhatian:</strong> Pastikan email valid dan aktif.
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="box">
            <div class="box-header">
                <h3>Form Ubah Email</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.pengaturan.actionemail.update', $email['ID_EMAIL']) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="NM_EMAIL">Alamat Email</label>
                        <input type="email" name="NM_EMAIL" id="NM_EMAIL" class="form-control" value="{{ old('NM_EMAIL', $email['NM_EMAIL']) }}" required>
                    </div>

                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    <a href="{{ route('admin.pengaturan.email') }}" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>

@include('admin.components.scripts')
</body>
</html>
