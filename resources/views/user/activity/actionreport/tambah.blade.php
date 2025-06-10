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

    @include('user.components.css')
    
</head>
<body>
    @include('user.components.sidebar')
<div class="padding">
 <!-- ############ PAGE START-->

<div class="container">
    <h2>ADD ACTIVITY</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info">
        <strong>Note:</strong> Harap periksa kembali dan pastikan semua data telah diisi dengan benar sebelum melanjutkan.
    </div>

    <form method="POST" action="{{ route('user.activity.report.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>Nama Company</label>
            <select class="form-control" name="company" required>
                <option value="">Pilih Company</option>
                @foreach($companies as $company)
                    <option value="{{ $company['ID_COMPANY'] }}" {{ $companyName == $company['NM_COMPANY'] ? 'selected' : '' }}>
                        {{ $company['NM_COMPANY'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Email Company</label>
            <input type="text" class="form-control" name="email" value="{{ $email }}" required>
        </div>

        <div class="form-group">
            <label>Nama User</label>
            <input type="text" class="form-control" name="username" value="{{ $username }}" required>
        </div>

        <div class="form-group">
            <label>Subject</label>
            <input type="text" class="form-control" name="Subject" value="Aktivitas Baru..." required>
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
            <textarea class="form-control" name="deskripsi" rows="10" required>Deskripsikan aktivitas Anda di sini...</textarea>
        </div>

        <button class="btn btn-primary">
            <i class="glyphicon glyphicon-saved"></i> Simpan
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const previewContainer = document.getElementById('preview');

        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            fileInput.files = files;
            previewFiles(files);
        });
        fileInput.addEventListener('change', () => previewFiles(fileInput.files));

        function previewFiles(files) {
            previewContainer.innerHTML = '';
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100px';
                        img.classList.add('mr-2', 'mb-2');
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
@include('user.components.scripts')

@include('user.components.themes')
</body>
</html>