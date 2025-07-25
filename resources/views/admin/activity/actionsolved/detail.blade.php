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
<div class="padding">
 <!-- ############ PAGE START-->
<table class="table table-striped b-t b-b dataTable no-footer display-inline">
    <tr>
        <th>Nama Company</th>
        <td>{{ $activity['NM_COMPANY'] ?? '-' }}</td>
    </tr>
    <tr>
        <th>Email Company</th>
        <td>{{ $activity['MAIL_COMPANY'] ?? '-' }}</td>
    </tr>
    <tr>
        <th>Nama User</th>
        <td>{{ $activity['NM_USER'] ?? '-' }}</td>
    </tr>
    <tr>
        <th>Subject</th>
        <td>{{ $activity['SUBJECT'] ?? '-' }}</td>
    </tr>
    <tr>
        <th>Foto</th>
        <td>
            @foreach ($fotos as $foto)
                <div style="margin-bottom:10px;">
                    <img src="{{ asset('storage/uploads/' . $foto['NM_ACTIVITY_FOTO']) }}" alt="Foto" width="100">
                    <br>
                    <a href="{{ asset('storage/uploads/' . $foto['NM_ACTIVITY_FOTO']) }}" target="_blank">View Full Image</a>
                </div>
            @endforeach
        </td>
    </tr>
    <tr>
        <th>Deskripsi</th>
        <td>{!! nl2br(e($activity['DESKRIPSI_SOLVED'] ?? '-')) !!}</td>
    </tr>
    <tr>
        <th>Tanggal Staus</th>
        <td>{{ $activity['TGL_SOLVED'] ?? '-' }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>{{ $activity['NM_STATUS'] ?? '-' }}</td>
    </tr>
</table>

<a href="{{ route('admin.activity.solved') }}" class="btn btn-primary">Kembali</a>


  <!-- ############ PAGE END-->
   
  </div>
</div>
@include('admin.components.scripts')

@include('admin.components.themes')
</body>
</html>
