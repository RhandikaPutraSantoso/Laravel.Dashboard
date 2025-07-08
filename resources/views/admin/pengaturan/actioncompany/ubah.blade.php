<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Ubah COMPANY - SAP HANA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  @include('admin.components.css')
</head>
<body>
@include('admin.components.sidebar')

<div class="padding">
    <div class="container">
        <h2>Ubah COMPANY</h2>
        <div class="alert alert-warning">
            <strong>Perhatian:</strong> Pastikan COMPANY valid dan efektif.
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
                <form action="{{ route('admin.pengaturan.actioncompany.update', $COMPANY['ID_COMPANY']) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="NM_COMPANY">Nama COMPANY</label>
                        <input type="text" name="NM_COMPANY" id="NM_COMPANY" class="form-control" value="{{ old('NM_COMPANY', $COMPANY['NM_COMPANY']) }}" required>
                    </div>

                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    <a href="{{ route('admin.pengaturan.company') }}" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>

@include('admin.components.scripts')
</body>
</html>
