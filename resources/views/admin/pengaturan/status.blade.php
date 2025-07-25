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

  <div class="container">
    <h2>STATUS SETTINGS</h2>
    <div class="alert alert-info">
        <strong>Note:</strong> This page displays the status settings for the application.
    </div>

    <div class="padding">
        
        <div class="box">
            <div class="box-header">
                <h2>STATUS Level</h2>
            </div>
            <div class="table-responsive" data-target="bg">
                <table id="table" class="table table-striped b-t b-b dataTable no-footer display-inline">
        <thead>
            <tr>
                <th>No</th>
                <th>Status Level</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($statusSettings as $index => $status)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $status['NM_STATUS'] }}</td>
                <td>
                    <a href="#" onclick="event.preventDefault(); if(confirm('Yakin ingin menghapus?')) { document.getElementById('delete-form-{{ $status['ID_STATUS'] }}').submit(); }" class="btn btn-danger btn-sm">
            <i class="glyphicon glyphicon-trash"></i> Hapus</a>

            <form id="delete-form-{{ $status['ID_STATUS'] }}" action="{{ route('admin.pengaturan.status.destroy', $status['ID_STATUS']) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>

                    <a href="{{ route('admin.pengaturan.actionstatus.ubah', $status['ID_STATUS'] ) }}" class="btn btn-warning btn-sm">Ubah</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="padding">
        <a href="{{ route('admin.pengaturan.actionstatus.tambah') }}" class="btn btn-primary mb-3"> + ADD STATUS LEVEL</a>
    </div>
</div>
            </div>
        </div>
    </div>
  </div>

  
 <!-- ############ PAGE END-->
   

@include('admin.components.scripts')


</body>
</html>
