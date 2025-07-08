
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
<div class="padding">
    <h2>STATUS FOR SAP</h2>
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
                            <th>Status</th>
                            <th>Tiket</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Subject</th>
                            <th>Foto</th>
                            <th>Deskripsi</th>
                            <th>Tanggal Proses</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $index => $activity)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $activity['NM_STATUS'] }}</td>
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
                            <td>{{ $activity['TGL_STATUS'] }}</td>
                            
                            <td>
                                <div class="btn-group-vertical">
                                    <a href="{{ route('user.activity.actionstatus.detail', ['id' => $activity['ID_ACTIVITY']]) }}" class="btn btn-info btn-sm">
                                        <i class="glyphicon glyphicon-eye-open"></i> Detail</a>

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
</div>



  <!-- ############ PAGE END-->
   

@include('user.components.scripts')

@include('user.components.themes')
<script>
$(document).ready(function () {
  var table = $('#table').DataTable({
    responsive: true,
    order: [[0, 'desc']],
    dom:
      "<'row mb-3'<'col-md-3'l><'col-md-6 text-center'B><'col-md-3'f>>" +
      "<'row'<'col-md-12'tr>>" +
      "<'row mt-2'<'col-md-5'i><'col-md-7'p>>",
    buttons: [
      { extend: 'csv', className: 'btn btn-outline-info btn-sm me-1' },
      { extend: 'excel', className: 'btn btn-outline-success btn-sm me-1' },
      { extend: 'pdf', className: 'btn btn-outline-danger btn-sm me-1' },
      { extend: 'print', className: 'btn btn-outline-primary btn-sm' }
    ],
    lengthMenu: [
      [5, 10, 25, 50, 100, -1],
      [5, 10, 25, 50, 100, "All"]
    ],
    language: {
      loadingRecords: "Loading...",
      zeroRecords: "Data tidak ditemukan",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
      infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
      search: "Search:",
      paginate: {
        next: "Next",
        previous: "Previous"
      }
    },
    createdRow: function (row, data, dataIndex) {
      var statusCell = $('td:eq(1)', row);
        // Format status cell with badge
      if (!statusCell.length) return; // Skip if no status cell found
        statusCell.addClass('text-center');
        statusCell.css('white-space', 'nowrap'); // Prevent text wrapping
        statusCell.css('font-weight', 'bold'); // Make text bold
        statusCell.css('text-transform', 'capitalize'); // Capitalize first letter of each word
        statusCell.css('font-size', '0.9em'); // Adjust font size for better readability
        if (statusCell.text().trim() === '') return; // Skip if status is empty
      var status = statusCell.text().trim();
      let badgeClass = '';

      switch (status) {
  case 'Not Continue':
  case 'Not Proses Yet':
    badgeClass = 'bg-danger';
    break;

  case 'Hard':
    badgeClass = 'bg-warning text-dark'; // Kuning
    break;

  case 'Basic':
    badgeClass = 'bg-primary'; // Biru
    break;

  case 'Expert':
    badgeClass = 'bg-dark text-white'; // Tambahan
    break;

  case 'Proses':
  case 'Proses MIS':
    badgeClass = 'bg-warning text-dark'; // Kuning proses
    break;

  case 'Solved':
    badgeClass = 'bg-success text-white'; // Hijau solved
    break;

  case 'Solved MIS':
    badgeClass = 'bg-success text-white'; // Hijau juga
    break;

  default:
    badgeClass = 'bg-secondary';
}


      statusCell.html(`<span class="badge ${badgeClass}">${status}</span>`);
    }
  });

  table.buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
});
</script>
</body>
</html>