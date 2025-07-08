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
    #chartWrapper {
        position: relative;
        padding-top: 50px;
        padding-left: 10px;
        width: auto;
        margin: 0 auto;
        color: var(--text-color);
    }

    .text-div-title {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        color: var(--text-color);
    }

    .text-div-ylabel {
        position: absolute;
        top: 50%;
        left: -50px;
        transform: translateY(-50%) rotate(-90deg);
        font-size: 12px;
        color: var(--text-color);
    }

    .text-div-footer {
        text-align: center;
        margin-top: 8px;
        font-size: 13px;
        color: var(--text-color);
    }
        .box {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            color: inherit;
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
        .chart-container {
            position: relative;
            width: 100%;
            height: 150px;
        }
        .chart-center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            font-size: 16px;
            pointer-events: none;
        }
    </style>
    
</head>
<body>
    @include('admin.components.sidebar')
    <div class="padding">
    <div class="padding">
        <div class="box">
            <div class="padding ">
                <div class="box">
                    <h1 class="text-center">Selamat Datang di Administrator CMNP GROUP Official</h1>
                    <br>
                    <h2 class="text-center">Dashboard</h2>
                    <!-- Form Filter -->
                    <form method="GET" class="form-filter" data-target="bg">
                        <label for="bulan" class="form-label">Bulan:</label>
                        <select name="bulan" class="form-select">
                            <option value="">Semua</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>

                        <label for="tahun" class="form-label">Tahun:</label>
                        <select name="tahun" class="form-select">
                            <option value="">Semua</option>
                            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>

                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>

                    <!-- CHART HTML -->
                        <div id="chartWrapper">
                            <div class="text-div-title">Aktivitas Perusahaan</div>
                            <div class="text-div-ylabel">Jumlah Aktivitas</div>
                            <canvas id="bar-chart"></canvas>
                            <div class="text-div-footer">Nama Perusahaan</div>
                        </div>

                        <!-- LEGEND -->
                        <div id="custom-legend" style="display:flex; flex-wrap:wrap; justify-content:center; margin-top:20px; gap:20px;"></div>

                        <!-- RESET BUTTON -->
                        <div style="text-align:center; margin-top:10px;">
                            <button id="resetChartBtn" class="btn btn-secondary" style="display: none;">Reset Chart</button>
                        </div>

                </div>
            </div>
        </div>
    </div>
    <!-- Task Report -->
    @php
        use Carbon\Carbon;
        $today = Carbon::now()->locale('id')->translatedFormat('d F Y');
    @endphp
    <div class="padding">
        <div class="box">
            <div class="box-header d-flex text-center justify-content-between">
                <div>
                    <h3 class="mb-0">Tasks</h3>
                    <small>Total Of Tasks</small>
                    <small>{{ $today }}</small>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <button id="refreshBtn" class="btn btn-sm btn-outline-secondary" title="Refresh">
                        <i class="material-icons">refresh</i>
                    </button>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-6">
                    <div class="chart-container" id="finishedChartContainer">
                        <canvas id="finishedChart"></canvas>
                    </div>
                    <div class="mt-2">
                        <strong>Finished</strong>
                        <div>{{ $finished ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="chart-container" id="remainingChartContainer">
                        <canvas id="remainingChart"></canvas>
                    </div>
                    <div class="mt-2">
                        <strong>Remaining</strong>
                        <div>{{ $remaining ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
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
        function renderCenterText(containerId, value) {
            const container = document.getElementById(containerId);
            const existing = container.querySelector('.chart-center-text');
            if (existing) existing.remove();
            const textDiv = document.createElement('div');
            textDiv.className = 'chart-center-text';
            textDiv.textContent = `${value}%`;
            container.appendChild(textDiv);
        }
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
                options: chartOptions
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
                options: chartOptions
            });
            renderCenterText('finishedChartContainer', finishedPercent);
            renderCenterText('remainingChartContainer', remainingPercent);
        }
        renderCharts();
        document.getElementById('refreshBtn').addEventListener('click', renderCharts);
    </script>
    <!-- Tabel -->
    <div class="padding">
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
    <!-- CHART.JS SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = {!! json_encode(array_column($dataChart, 'NM_COMPANY')) !!};
    const data = {!! json_encode(array_map('intval', array_column($dataChart, 'JUMLAH'))) !!};

       const logoMap = {
        "citra marga nusaphala persada": "/layouts/assets/images/cmnp.png",
        "citra marga nusantara propertindo": "/layouts/assets/images/cmnproper.png",
        "citra margatama surabaya": "/layouts/assets/images/cms.png",
        "citra persada infrastruktur": "/layouts/assets/images/cpi.png",
        "citra marga lintas jabar": "/layouts/assets/images/cmlj.png",
        "citra waspphutowa": "/layouts/assets/images/cw.jpg",
        "citra karya jabar tol": "/layouts/assets/images/ckjt.jpg",
        "girder indonesia": "/layouts/assets/images/gi.png",
    };


    const colors = [
        '#3498db', '#1abc9c', '#f1c40f',
        '#9b59b6', '#e74c3c', '#95a5a6', '#ff9f40'
    ];

    let currentChart;
    const resetBtn = document.getElementById('resetChartBtn');

    function renderChart(chartLabels, chartData, colorList) {
        const ctx = document.getElementById('bar-chart').getContext('2d');
        const textColor = getComputedStyle(document.body).getPropertyValue('text-color') || '#777777';

        if (currentChart) currentChart.destroy();

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
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            font: { size: 14 }
                        },
                        grid: {
                            color: '#555555'
                        }
                    },
                    x: {
                        ticks: {
                            color: textColor,
                            font: { size: 12 }
                        },
                        grid: {
                            color: '#555555'
                        }
                    }
                }
            }
        });
    }

    // Init chart
    renderChart(labels, data, colors);

    // Custom legend
    const legend = document.getElementById('custom-legend');
    labels.forEach((label, index) => {
        const item = document.createElement('div');
        item.style.cssText = "display:flex; flex-direction:column; align-items:center; font-size:12px; cursor:pointer; margin:8px; width:70px";

        const img = document.createElement('img');
        img.src = logoMap[label.toLowerCase()] || '/assets/foto_logo_perusahaan/default.png';
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

    // Reset chart
    resetBtn.addEventListener('click', () => {
        renderChart(labels, data, colors);
        resetBtn.style.display = 'none';
    });
</script>

    @include('admin.components.scripts')
    @include('admin.components.themes')

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
});

  table.buttons().container().appendTo('#table_wrapper .col-md-6:eq(0)');
});
</script>
</body>
</html>