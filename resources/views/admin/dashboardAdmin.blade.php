
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
@include('admin.components.scripts')

@include('admin.components.themes')
</body>
</html>