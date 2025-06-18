
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
    .box {
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  color: 'inherit';
}

   .box .col-6 {
    padding: 10px;
  }

  .box .col-6 canvas {
    margin: auto;
    display: block;
    max-width: 150px;
    max-height: 150px;
  }

  .box .btn {
    font-size: 11px;
    padding: 3px 10px;
  }

  .box .mt-2 div {
    font-size: 14px;
  }

  .box .mb-0 {
    font-size: 16px;
    font-weight: 600;
  }

  .box small {
    font-size: 12px;
  
  }
  
</style>

</head>
<body>
    @include('admin.components.sidebar')
<div class="padding white">
    
    <h1>Selamat Datang di Administrator CMNP GROUP Official </h1>
    <br>

    <!-- Form Filter -->
    <form method="GET" class="m-b-lg white">
        <label for="bulan">Bulan:</label>
        <select name="bulan" class="display-inline ">
            <option value="">Semua</option>
            @for ($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                </option>
            @endfor
        </select>

        <label for="tahun">Tahun:</label>
        <select name="tahun" class=" display-inline ">
            <option value="">Semua</option>
            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <!-- Chart -->
    <canvas id="bar-chart"></canvas>
    <div id="custom-legend" class="white" style="display:flex; flex-wrap:wrap; justify-content:center; margin-top:20px; gap:20px;"></div>

    <!-- Tombol Reset -->
    <div style="text-align:center; margin-top:10px;">
        <button id="resetChartBtn" class="btn btn-secondary" style="display: none;">Reset Chart</button>
    </div>

   <!--Task Report-->
@php
    use Carbon\Carbon;
    $today = Carbon::now()->locale('id')->translatedFormat('d F Y'); 
@endphp

<div class="box">
    <div class="box-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0">Tasks</h3>
            <small>Total Of Tasks</small>
            <small >{{ $today }}</small>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <!-- Tombol Refresh -->
            <button id="refreshBtn" class="btn btn-sm btn-outline-secondary" title="Refresh">
                <i class="material-icons">refresh</i>
            </button>
        </div>
    </div>

    <div class="row no-gutters text-center" style="padding: 20px 0;">
        <div class="col-6">
            <canvas id="finishedChart" style="height: 120px;"></canvas>
            <div class="mt-2">
                <strong>Finished</strong>
                <div>{{ $finished ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6">
            <canvas id="remainingChart" style="height: 120px;"></canvas>
            <div class="mt-2">
                <strong>Remaining</strong>
                <div>{{ $remaining ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>



    <!-- Tabel -->
    <br><br>
    <div class="padding ">
    <div class="box">
        <div class="box-header">
            <h2>Aktivitas Perusahaan</h2>
        </div>
        <div class="table-responsive" data-target="bg">
            <table id="table" class="table table-striped b-t b-b dataTable no-footer display-inline">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aktivitas as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['NM_COMPANY'] }}</td>
                            <td>{{ $item['MAIL_COMPANY'] }}</td>
                            <td>{{ $item['NM_USER'] }}</td>
                            <td>{{ $item['TGL_ACTIVITY'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<!-- Chart Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = {!! json_encode(array_column($dataChart, 'NM_COMPANY')) !!};
    const data = {!! json_encode(array_map('intval', array_column($dataChart, 'JUMLAH'))) !!};

    const logoMap = {
        "cmnp": "/layouts/assets/images/cmnp.png",
        "cmnpproper": "/layouts/assets/images/cmnproper.png",
        "cms": "/layouts/assets/images/cms.png",
        "cpi": "/layouts/assets/images/cpi.png",
        "cmlj": "/layouts/assets/images/cmlj.png",
        "cw": "/layouts/assets/images/cw.jpg",
        "ckjt": "/layouts/assets/images/ckjt.jpg"
    };

    const colors = [
        '#3498db', '#1abc9c', '#f1c40f',
        '#9b59b6', '#e74c3c', '#95a5a6', '#ff9f40'
    ];

    let currentChart;
    const resetBtn = document.getElementById('resetChartBtn');

    function renderChart(chartLabels, chartData, colorList) {
        if (currentChart) currentChart.destroy();
        const ctx = document.getElementById('bar-chart').getContext('2d');
        currentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Jumlah Aktivitas',
                    data: chartData,
                    backgroundColor: colorList,
                    borderColor: colorList,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Aktivitas Perusahaan',
                        font: { size: 18 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Jumlah Aktivitas' }
                    }
                }
            }
        });
    }

    renderChart(labels, data, colors);

    const legend = document.getElementById('custom-legend');
    labels.forEach((label, index) => {
        const item = document.createElement('div');
        item.style.cssText = "display:flex; flex-direction:column; align-items:center; font-size:12px; cursor:pointer";

        const img = document.createElement('img');
        img.src = logoMap[label] || '/assets/foto_logo_perusahaan/default.png';
        img.style.cssText = "width:50px; height:50px; object-fit:contain; margin-bottom:5px; border-radius:8px";

        const caption = document.createElement('div');
        caption.innerText = label;
        caption.style.textAlign = 'center';

        item.appendChild(img);
        item.appendChild(caption);

        item.onclick = () => {
            renderChart([label], [data[index]], [colors[index % colors.length]]);
            resetBtn.style.display = 'inline-block';
        };

        legend.appendChild(item);
    });

    resetBtn.addEventListener('click', () => {
        renderChart(labels, data, colors);
        resetBtn.style.display = 'none';
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw(chart) {
            const { width, height } = chart;
            const ctx = chart.ctx;
            ctx.clearRect(0, 0, width, height);
            const dataset = chart.data.datasets[0].data;
            const total = dataset.reduce((a, b) => a + b, 0);
            const percent = Math.round((dataset[0] / total) * 100) + '%';

            ctx.save();
            ctx.font = 'bold 16px sans-serif';
            ctx.fillStyle = '';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(percent, width / 2, height / 2);
            ctx.restore();
        }
    };

    Chart.register(centerTextPlugin);

    const chartOptions = {
        cutout: '70%',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: { enabled: false },
            legend: { display: false }
        }
    };

    let finishedChart, remainingChart;

    function renderCharts() {
        const finishedPercent = {{ $finishedPercent ?? 0 }};
        const remainingPercent = {{ $remainingPercent ?? 0 }};

        if (finishedChart) finishedChart.destroy();
        if (remainingChart) remainingChart.destroy();

        finishedChart = new Chart(document.getElementById('finishedChart'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [finishedPercent, 100 - finishedPercent],
                    backgroundColor: ['#1abc9c', '#eeeeee'],
                    borderWidth: 7
                }]
            },
            options: chartOptions,
            plugins: [centerTextPlugin]
        });

        remainingChart = new Chart(document.getElementById('remainingChart'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [remainingPercent, 100 - remainingPercent],
                    backgroundColor: ['#f1c40f', '#eeeeee'],
                    borderWidth: 7
                }]
            },
            options: chartOptions,
            plugins: [centerTextPlugin]
        });
    }

    // Render awal
    renderCharts();

    // Event tombol refresh
    document.getElementById('refreshBtn').addEventListener('click', function () {
        renderCharts(); // bisa ditambahkan fetch data terbaru via AJAX kalau mau
    });
</script>


@include('admin.components.scripts')

@include('admin.components.themes')
</body>
</html>