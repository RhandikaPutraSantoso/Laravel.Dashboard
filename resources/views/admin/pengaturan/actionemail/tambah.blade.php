<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>SAP HANA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-barstyle" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Flatkit">
  <meta name="mobile-web-app-capable" content="yes">

    @include('admin.components.css')
</head>
<body>
    @include('admin.components.sidebar')
<div class="padding">
 <!-- ############ PAGE START-->

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container">
    <h3>Tambah Email Baru</h3>
    <hr>

    <form action="{{ route('admin.pengaturan.email.store') }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label for="email">Email Baru:</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.pengaturan.email') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

  <!-- ############ PAGE END-->
   
  </div>
@include('admin.components.scripts')

@include('admin.components.themes')
</body>
</html>