
<script src="{{ asset('layouts/libs/jquery/jquery/dist/jquery.js') }}"></script>
<script src="{{ asset('layouts/libs/jquery/tether/dist/js/tether.min.js') }}"></script>
<script src="{{ asset('layouts/libs/jquery/bootstrap/dist/js/bootstrap.js') }}"></script>
<script src="{{ asset('layouts/libs/jquery/underscore/underscore-min.js') }}"></script>
<script src="{{ asset('layouts/libs/jquery/jQuery-Storage-API/jquery.storageapi.min.js') }}"></script>
<script src="{{ asset('layouts/libs/jquery/PACE/pace.min.js') }}"></script>

<script src="{{ asset('layouts/scripts/config.lazyload.js') }}"></script>
<script src="{{ asset('layouts/scripts/palette.js') }}"></script>
<script src="{{ asset('layouts/scripts/ui-load.js') }}"></script>
<script src="{{ asset('layouts/scripts/ui-jp.js') }}"></script>
<script src="{{ asset('layouts/scripts/ui-include.js') }}"></script>
<script src="{{ asset('layouts/scripts/ui-device.js') }}"></script>
<script src="{{ asset('layouts/scripts/ui-form.js') }}"></script>
<script src="{{ asset('layouts/scripts/ui-nav.js') }}"></script>
<script src="{{ asset('layouts/scripts/ui-screenfull.js') }}"></script>
<script src="{{ asset('layouts/scripts/ui-scroll-to.js') }}"></script>
<script src="{{ asset('layouts/scripts/ui-toggle-class.js') }}"></script>
<script src="{{ asset('layouts/scripts/app.js') }}"></script>
<script src="{{ asset('layouts/scripts/jquery-1.10.2.js') }}"></script>
<script src="{{ asset('layouts/scripts/ckeditor.js')}}"></script>

<!-- DataTables & dependencies (gunakan salah satu versi saja, disarankan yang lokal jika tersedia) -->
<script src="{{ asset('layouts/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('layouts/DataTables/DataTables-1.10.18/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('layouts/DataTables/Buttons-1.5.6/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('layouts/DataTables/Buttons-1.5.6/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('layouts/DataTables/JSZip-2.5.0/jszip.min.js') }}"></script>
<script src="{{ asset('layouts/DataTables/pdfmake-0.1.36/pdfmake.min.js') }}"></script>
<script src="{{ asset('layouts/DataTables/pdfmake-0.1.36/vfs_fonts.js') }}"></script>
<script src="{{ asset('layouts/DataTables/Buttons-1.5.6/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('layouts/DataTables/Buttons-1.5.6/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('layouts/DataTables/Buttons-1.5.6/js/buttons.colvis.min.js') }}"></script>
<script src="{{ asset('layouts/DataTables/Buttons-1.5.6/js/buttons.flash.min.js') }}"></script>
<script src="{{ asset('layouts/DataTables/Buttons-1.5.6/js/buttons.jqueryui.min.js') }}"></script>

<script src="{{ asset('layouts/scripts/datatable.js') }}"></script>
<script>
function loadNotifications() {
  $.ajax({
    url: '{{ url("/get-notifications") }}',
    method: 'GET',
    success: function (res) {
      // Update jumlah di sidebar
      let totalCount = res.report_count + res.status_count + res.solved_count;
      $('#notif-count').text(totalCount);
      $('#activity-sap-count').text(totalCount);

      $('#activity-sap-sub-count').text(res.report_count);
      $('#activity-status-sub-count').text(res.status_count);
      $('#activity-solved-sub-count').text(res.solved_count);

      // Dropdown isi
      let html = '';

      if (res.report.length > 0) {
        html += `<div class="dropdown-item text-primary font-weight-bold">Activity Report</div>`;
        res.report.forEach(item => {
          let url = `{{ route('admin.activity.actionreport.ubah', ':id') }}`.replace(':id', item.ID_ACTIVITY);
          html += `<a class="dropdown-item" href="${url}">No Tiket: ${item.TIKET}</a>`;
        });
      }

      if (res.status.length > 0) {
        html += `<div class="dropdown-item text-warning font-weight-bold mt-2">Activity Status</div>`;
        res.status.forEach(item => {
          let url = `{{ route('admin.activity.actionstatus.ubah',':id') }}`.replace(':id', item.ID_ACTIVITY);
          html += `<a class="dropdown-item" href="${url}">No Tiket: ${item.TIKET}</a>`;
        });
      }

      if (res.solved.length > 0) {
        html += `<div class="dropdown-item text-success font-weight-bold mt-2">Activity Solved</div>`;
        res.solved.forEach(item => {
          let url = `{{ route('admin.activity.actionsolved.ubah',':id') }}`.replace(':id', item.ID_ACTIVITY);
          html += `<a class="dropdown-item" href="${url}">No Tiket: ${item.TIKET}</a>`;
        });
      }

      if (totalCount === 0) {
        html = '<div class="dropdown-item text-muted">Tidak ada notifikasi baru</div>';
      }

      $('#notif-items').html(html);
    }
  });
}
loadNotifications();
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const logContainer = document.getElementById('activityLogCollapse');

        fetch("{{ url('/admin/activity/fetch-log') }}")
            .then(response => response.json())
            .then(data => {
                logContainer.innerHTML = '';

                if (!data.logs || data.logs.length === 0) {
                    logContainer.innerHTML = '<li><small class="text-muted">Tidak ada log aktivitas</small></li>';
                } else {
                    data.logs.forEach(log => {
                        const li = document.createElement('li');
                        li.innerHTML = `<small>${log.log_message}</small>`;
                        logContainer.appendChild(li);
                    });
                }
            })
            .catch(error => {
                logContainer.innerHTML = '<li><small class="text-danger">Gagal memuat data</small></li>';
                console.error('Log fetch error:', error);
            });
    });
</script>





