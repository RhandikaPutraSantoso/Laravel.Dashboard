
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
    <h2>REQUESTS FOR SAP</h2>
    <div class="padding">
        <div class="box">
            <div class="box-header">
                <h2>Aktivitas Perusahaan</h2>
            </div>
            <div class="table-responsive" data-target="bg">
                <table id="table" class="table table-striped b-t b-b dataTable no-footer display-inline">
                    <thead >
                        
                        <tr>
                            <th>No</th>
                            <th>Tiket</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Subject</th>
                            <th>Foto</th>
                            <th>Deskripsi</th>
                            <th>Tanggal Terkirim</th>
                            <th>Komentar</th>
                            <th>Tanggal Komentar</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $index => $activity)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $activity['TIKET'] }}</td>
                            <td>{{ $activity['NM_COMPANY'] }}</td>
                            <td>{{ $activity['MAIL_COMPANY'] }}</td>
                            <td>{{ $activity['NM_USER'] }}</td>
                            <td>{{ $activity['SUBJECT'] }}</td>
                            <td>
                                @if ($activity['FOTO'])
                                    <img src="{{ asset('storage/uploads/' . $activity['FOTO']) }}" alt="Foto" width="100" class="rounded">
                                @else
                                    <small class="text-muted">No image</small>
                                @endif
                            </td>
                            <td>{!! nl2br(e($activity['DESKRIPSI'])) !!}</td>
                            <td>{{ $activity['TGL_ACTIVITY'] }}</td>
                            <td>{!! nl2br(e($activity['KOMENTAR'])) !!}</td>
                            <td>{{ $activity['TGL_KOMENTAR'] }}</td>
                            <td>{{ $activity['NAMA_KATEGORI'] }}</td>
                            <td>
                                <div class="btn-group-vertical">
                                    <a href="{{ route('admin.activity.actionreport.detail', ['id' => $activity['ID_ACTIVITY']]) }}" class="btn btn-info btn-sm">
                                        <i class="glyphicon glyphicon-eye-open"></i> Detail</a>

                                    <a href="{{ route('admin.activity.actionreport.ubah', $activity['ID_ACTIVITY']) }}" class="btn btn-warning btn-sm">
                                        <i class="glyphicon glyphicon-edit"></i> Ubah</a>
                                    
                                    <a href="#" class="btn btn-danger btn-sm"onclick="event.preventDefault(); if(confirm('Yakin ingin menghapus?')) { document.getElementById('delete-form-{{ $activity['ID_ACTIVITY'] }}').submit(); }"><i class="glyphicon glyphicon-trash"></i> Hapus</a>

                                    <form id="delete-form-{{ $activity['ID_ACTIVITY'] }}"
                                        action="{{ route('admin.activity.report.destroy', $activity['ID_ACTIVITY']) }}"
                                        method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>


                                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=rhndkputr@gmail.com&su={{ urlencode($activity['SUBJECT']) }}&body={{ urlencode(strip_tags($activity['DESKRIPSI']) . "\n\nFoto: https://sap.cmnp.co.id/foto_produk/" . ($activity['FOTO'] ?? 'Tidak tersedia')) }}" target="_blank" class="btn btn-success btn-sm">
                                        <i class="glyphicon glyphicon-envelope"></i> Kirim Email
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
              <a href="{{ route('admin.activity.actionreport.tambah') }}" class="btn btn-primary mb-3"> + ADD ACTIVITY</a>
              
            </div>
        </div>
    </div>
</div>



  <!-- ############ PAGE END-->
   

@include('admin.components.scripts')

@include('admin.components.themes')
</body>
</html>