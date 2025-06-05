<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>SAP HANA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Flatkit">
  <meta name="mobile-web-app-capable" content="yes">
    @include('admin.components.css')

</head>
<body>
    @include('admin.components.sidebar')
  <div class="padding">
    <h2>CHANGE ACTIVITY</h2>

    <form method="POST" action="{{ route('admin.activity.actionreport.update', ['id' => $activity['ID_ACTIVITY']]) }}" enctype="multipart/form-data" >
      @csrf
    

      <div class="form-group">
        <label>Nama Company (Jangan Sampai Salah)</label>
        <select class="form-control" name="company" required>
          <option value="">Pilih Company</option>
          @foreach ($datacompany as $value)
            <option value="{{ $value['ID_COMPANY'] }}" {{ $activity['ID_COMPANY'] == $value['ID_COMPANY'] ? 'selected' : '' }}>
              {{ $value['NM_COMPANY'] }}
            </option>
          @endforeach
        </select>
        @error('company')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label>Email Company</label>
        <input type="text" class="form-control" name="email" value="{{ old('email', $activity['MAIL_COMPANY']) }}">
        @error('email')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label>Nama User</label>
        <input type="text" class="form-control" name="username" value="{{ old('username', $activity['NM_USER']) }}">
        @error('username')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label>Subject</label>
        <input type="text" class="form-control" name="subject" value="{{ old('subject', $activity['SUBJECT']) }}">
        @error('subject')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

 @foreach ($daftar_foto as $foto)
    <div style="display: inline-block; position: relative; margin: 5px;">
        <img src="{{ asset('storage/uploads/' . $foto['NM_ACTIVITY_FOTO']) }}" alt="Foto" style="width:100px;height:80px;border-radius:10px;box-shadow: 0 2px 6px rgba(0,0,0,0.3);">
        <button 
            type="submit" 
            name="hapus_foto" 
            value="{{ $foto['ID_ACTIVITY_FOTO'] }}"
            onclick="return confirm('Yakin ingin menghapus foto ini?')"
            style="position: absolute; top: -8px; right: -8px; background-color:red; color:white; border:none; border-radius:50%; width:22px; height:22px; font-size:14px; cursor:pointer;">
            ×
        </button>
    </div>
@endforeach


      <div class="form-group">
        <label>Tambah Foto Baru</label>
        <input type="file" class="form-control" name="foto_baru[]" multiple>
        <small class="form-text text-muted">Anda dapat memilih beberapa foto.</small>
        @error('foto_baru.*')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control" rows="8">{{ old('deskripsi', $activity['DESKRIPSI']) }}</textarea>
        @error('deskripsi')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label>Komentar</label>
        <textarea name="komentar" class="form-control" id="komentar" rows="8">{{ old('komentar', $activity['KOMENTAR']) }}</textarea>
        @error('komentar')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <div class="form-group">
        <label>Nama Kategori</label>
        <select class="form-control" name="ID_KATEGORI" required>
          <option value="">Pilih Kategori</option>
          @foreach ($datakategori as $value)
            <option value="{{ $value['ID_KATEGORI'] }}" {{ $activity['ID_KATEGORI'] == $value['ID_KATEGORI'] ? 'selected' : '' }}>
              {{ $value['NAMA_KATEGORI'] }}
            </option>
          @endforeach
        </select>
        @error('ID_KATEGORI')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>

      <button class="btn btn-primary" name="ubah" >Ubah</button>
    </form>
  </div>

@include('admin.components.scripts')

@include('admin.components.themes')
</body>
</html>
