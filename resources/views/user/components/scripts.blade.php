
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
// Fungsi untuk ubah waktu jadi format "x menit lalu"
function timeAgo(datetime) {
  const now = new Date();
  const then = new Date(datetime);
  const seconds = Math.floor((now - then) / 1000);

  if (seconds < 60) return 'Baru saja';
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes} menit lalu`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours} jam lalu`;
  const days = Math.floor(hours / 24);
  return `${days} hari lalu`;
}

function loadRecentActivityTimeline() {
  const $logContainer = $('#activityLogCollapse');
  const $loadingItem = $('#activityLogLoading');

  // Tampilkan loading jika belum ada
  if ($loadingItem.length === 0) {
    $logContainer.prepend('<li id="activityLogLoading"><small class="text-muted">Memuat aktivitas...</small></li>');
  }

  $.ajax({
    url: '{{ url("/admin/activity/timeline") }}', // URL baru
    method: 'GET',
    success: function (res) {
      $('#activityLogLoading').remove();
      $logContainer.find('li').remove(); // hapus semua log sebelumnya

      if (!res.logs || res.logs.length === 0) {
        $logContainer.append('<li><small class="text-muted">Tidak ada log aktivitas</small></li>');
      } else {
        res.logs.forEach(log => {
          const waktu = timeAgo(log.log_time);
          $logContainer.append(`<li><small>${log.log_message}<br><span class="text-muted">${waktu}</span></small></li>`);
        });
      }
    },
    error: function (xhr, status, error) {
      $('#activityLogLoading').remove();
      $logContainer.append('<li><small class="text-danger">Gagal memuat data aktivitas</small></li>');
      console.error('Fetch error:', error);
    }
  });
}

// Load saat pertama kali
document.addEventListener('DOMContentLoaded', function () {
  loadRecentActivityTimeline();
});
</script>
