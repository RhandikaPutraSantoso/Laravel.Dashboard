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
    <style>

.drop-zone {
    border: 3px dashed #ccc;
    border-radius: 20px;
    padding: 40px;
    text-align: center;
    color: #666;
    background-color: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    font-size: 1.1rem;
    position: relative;
}

.drop-zone.dragover {
    border-color: #4aa8ff;
    background-color: #e0f3ff;
    color: #333;
}

.preview-container {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
}

.preview-container img {
    width: 100px;
    height: 100px;
    border-radius: 12px;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transition: transform 0.2s ease;
}

.preview-container img:hover {
    transform: scale(1.05);
}
</style>
</head>
<body>
    @include('admin.components.sidebar')
<div class="padding">
 <!-- ############ PAGE START-->

<h2>ADD ACTIVITY</h2>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" enctype="multipart/form-data" action="{{ route('admin.activity.report.store') }}">
    @csrf

    <div class="form-group">
        <label>Nama Company</label>
        <select class="form-control" name="company" required>
            <option value="">Pilih Company</option>
            @foreach ($datacompany as $value)
                <option value="{{ $value['ID_COMPANY'] }}">{{ $value['NM_COMPANY'] }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Email Company</label>
        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
    </div>

    <div class="form-group">
        <label>Nama User</label>
        <input type="text" class="form-control" name="username" value="{{ old('username') }}" required>
    </div>

    <div class="form-group">
        <label>Subject</label>
        <input type="text" class="form-control" name="Subject" value="{{ old('Subject', 'Aktivitas Baru...') }}" required>
    </div>

    <div class="form-group">
        <label>Upload Foto</label>
        <div class="dz-message" ui-jp="dropzone" >
        <div id="drop-zone" class="drop-zone dropzone white" >
            <h4 class="m-t-lg m-b-md">Drop files here or click to upload.</h4>
            <input type="file" id="file-input" name="foto[]" multiple accept="image/*" class="drop-zone-input" style="display:none;">
            <div class="drop-zone-icon">
                <i class="glyphicon glyphicon-upload" style="font-size: 48px; color: #888;"></i>
            </div>
            
            <div id="preview" class="preview-container"></div>
        </div>
        </div>
    </div>

    <div class="form-group">
        <label>Deskripsi</label>
        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="10" required>{{ old('deskripsi', 'Deskripsikan aktivitas Anda di sini...') }}</textarea>
    </div>

    <div class="form-group">
        <label>Nama Kategori</label>
        <select class="form-control" name="ID_KATEGORI" required>
            <option value="">Pilih Kategori</option>
            @foreach ($datakategori as $value)
                <option value="{{ $value['ID_KATEGORI'] }}">{{ $value['NAMA_KATEGORI'] }}</option>
            @endforeach
        </select>
    </div>

    <button class="btn btn-primary" name="save"><i class="glyphicon glyphicon-saved"></i> Simpan</button>
</form>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const subjectInput = document.querySelector("input[name='Subject']");
  const deskripsiInput = document.querySelector("textarea[name='deskripsi']");
  const defaultSubject = "Aktivitas Baru...";
  const defaultDeskripsi = "Deskripsikan aktivitas Anda di sini...";

  subjectInput.addEventListener("focus", function () {
    if (this.value === defaultSubject) this.value = "";
  });
  deskripsiInput.addEventListener("focus", function () {
    if (this.value === defaultDeskripsi) this.value = "";
  });

  subjectInput.addEventListener("blur", function () {
    if (this.value.trim() === "") this.value = defaultSubject;
  });
  deskripsiInput.addEventListener("blur", function () {
    if (this.value.trim() === "") this.value = defaultDeskripsi;
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('preview');

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        fileInput.files = files;
        previewFiles(files);
    });

    fileInput.addEventListener('change', () => {
        previewFiles(fileInput.files);
    });

    function previewFiles(files) {
        previewContainer.innerHTML = '';
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '100px';
                    img.style.margin = '5px';
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

  <!-- ############ PAGE END-->
   
  </div>
@include('admin.components.scripts')

@include('admin.components.themes')
</body>
</html>