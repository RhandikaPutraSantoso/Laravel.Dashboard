
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
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
        {{ session('error') }}
    </div>
@endif

<script>
    setTimeout(function() {
        var successAlert = document.getElementById('success-alert');
        var errorAlert = document.getElementById('error-alert');

        if (successAlert) {
            successAlert.style.transition = "opacity 0.5s ease-out";
            successAlert.style.opacity = 0;
            setTimeout(() => successAlert.remove(), 500);
        }

        if (errorAlert) {
            errorAlert.style.transition = "opacity 0.5s ease-out";
            errorAlert.style.opacity = 0;
            setTimeout(() => errorAlert.remove(), 500);
        }
    }, 5000); // 5 detik
</script>

    <h2>SOLVED FOR SAP</h2>
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
                            <th>Deskripsi Solved</th>
                            <th>Tanggal Solaved</th>
                            <th>Status</th>
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
                            <td>{!! nl2br(e($activity['DESKRIPSI_SOLVED'])) !!}</td>
                            <td>{{ $activity['TGL_SOLVED'] }}</td>
                            <td>{{ $activity['NM_STATUS'] }}</td>
                            
                            <td>
                                <div class="btn-group-vertical">
                                    <a href="{{ route('admin.activity.actionsolved.detail', ['id' => $activity['ID_ACTIVITY']]) }}" class="btn btn-info btn-sm">
                                        <i class="glyphicon glyphicon-eye-open"></i> Detail</a>

                                    <a href="{{ route('admin.activity.actionsolved.ubah', $activity['ID_ACTIVITY']) }}" class="btn btn-warning btn-sm">
                                        <i class="glyphicon glyphicon-edit"></i> Ubah</a>
                                    
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


  <!-- ############ PAGE END-->
   

@include('admin.components.scripts')

@include('admin.components.themes')
</body>
</html>