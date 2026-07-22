<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Penilaian Supplier – {{ $companyName }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #2d3748;
            background-color: #f1f5f9;
            padding: 24px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Sheet that mimics a paper page on screen */
        .sheet {
            background: #ffffff;
            max-width: 900px;
            margin: 0 auto;
            padding: 32px 40px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            border-radius: 8px;
        }

        /* ── Toolbar (hidden when printing) ─────────────────────── */
        .toolbar {
            max-width: 900px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .toolbar button,
        .toolbar a {
            cursor: pointer;
            border: none;
            font-size: 13px;
            font-weight: bold;
            padding: 9px 18px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print {
            background-color: #009688;
            color: #ffffff;
        }

        .btn-back {
            background-color: #e2e8f0;
            color: #475569;
        }

        /* ── Header / Kop Surat ─────────────────────────────────── */
        .header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #009688;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }

        .header-logo img {
            width: 70px;
            height: auto;
        }

        .header-text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 14px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #00695C;
            letter-spacing: 0.5px;
        }

        .document-title {
            font-size: 13px;
            color: #4a5568;
            margin-top: 3px;
        }

        .document-subtitle {
            font-size: 10px;
            color: #718096;
            margin-top: 2px;
        }

        .section-label {
            font-size: 11px;
            font-weight: bold;
            color: #00695C;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
            margin-top: 20px;
        }

        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .ranking-table thead tr {
            background-color: #009688;
            color: #ffffff;
        }

        .ranking-table thead th {
            padding: 9px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.4px;
            border: 1px solid #007a6e;
        }

        .ranking-table thead th.text-center { text-align: center; }
        .ranking-table thead th.text-right { text-align: right; }

        .ranking-table tbody tr { border-bottom: 1px solid #e2e8f0; }
        .ranking-table tbody tr:nth-child(even) { background-color: #f0fafa; }
        .ranking-table tbody tr:nth-child(odd) { background-color: #ffffff; }

        .ranking-table tbody td {
            padding: 8px 12px;
            font-size: 11px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .ranking-table tbody td.text-center { text-align: center; }
        .ranking-table tbody td.text-right { text-align: right; }

        .rank-badge {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }

        .rank-first { background-color: #009688; color: #ffffff; }
        .rank-other { background-color: #e2e8f0; color: #4a5568; }

        .recommendation-badge {
            display: inline-block;
            font-size: 9px;
            font-weight: bold;
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fbbf24;
            border-radius: 3px;
            padding: 1px 5px;
            margin-left: 6px;
        }

        .nilai-cell { font-weight: bold; color: #009688; }

        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            margin-top: 20px;
            font-size: 9px;
            color: #a0aec0;
            text-align: center;
        }

        .meta-info {
            margin-bottom: 16px;
            font-size: 10px;
            color: #718096;
        }

        .meta-info span { margin-right: 20px; }
        .meta-info strong { color: #4a5568; }

        .produk-list {
            font-size: 10px;
            color: #4a5568;
            line-height: 1.4;
        }

        .produk-item {
            margin-bottom: 2px;
        }

        .produk-item strong {
            color: #2d3748;
        }

        .chart-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .chart-box {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            background-color: #f8fafc;
        }

        .chart-title {
            font-size: 11px;
            font-weight: bold;
            color: #00695C;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            text-align: center;
        }

        .chart-wrapper {
            max-width: 250px;
            margin: 0 auto 12px;
        }

        .weight-legend {
            font-size: 9px;
            line-height: 1.6;
        }

        .weight-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .weight-label {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .weight-color {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            border: 1px solid rgba(0,0,0,0.15);
        }

        .weight-value {
            font-weight: bold;
            color: #4a5568;
        }

        .chart-image-print {
            display: none;
        }

        /* ── Print rules ────────────────────────────────────────── */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .toolbar { display: none !important; }
            .sheet {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
            tr { page-break-inside: avoid; }
            .chart-container { page-break-inside: avoid; }
            canvas { display: none !important; }
            .chart-image-print { 
                display: block !important;
                max-width: 250px;
                margin: 0 auto;
            }
        }

        @page {
            margin: 1.5cm;
        }
    </style>
</head>
<body>

    {{-- ── Toolbar (layar saja) ────────────────────────────────────── --}}
    <div class="toolbar">
        <a href="{{ route('supervisor.laporan.penilaian') }}" class="btn-back">&larr; Kembali</a>
        <button onclick="window.print()" class="btn-print">🖨 Cetak / Simpan PDF</button>
    </div>

    {{-- ── Main Document ─────────────────────────────────────────── --}}
    <div class="sheet">
        {{-- Header / Kop --}}
        <div class="header">
            <div class="header-logo">
                <img src="https://tse3.mm.bing.net/th/id/OIP.Ahdo6zqgNFfe3oaEKkS5ewHaBh?pid=Api&P=0&h=180"
                     alt="Logo {{ $companyName }}">
            </div>
            <div class="header-text">
                <div class="company-name">{{ $companyName }}</div>
                <div class="document-title">Hasil Penilaian Supplier – Analytic Hierarchy Process (AHP)</div>
                <div class="document-subtitle">Laporan Resmi Pemeringkatan Kelayakan Supplier</div>
            </div>
        </div>

        {{-- Meta info --}}
        <div class="meta-info">
            <span><strong>Tanggal Laporan:</strong> {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</span>
            <span><strong>Metode:</strong> Analytic Hierarchy Process (AHP)</span>
            <span><strong>Jumlah Supplier:</strong> {{ $rankings->count() }}</span>
        </div>

        {{-- Chart Section: Bobot Kriteria & Bobot Global Subkriteria --}}
        @if($rankings->isNotEmpty() && !empty($kriteriaWeights) && !empty($globalSubkriteriaWeights))
            <div class="section-label">Visualisasi Bobot</div>
            <div class="chart-container">
                {{-- Bobot Kriteria --}}
                <div class="chart-box">
                    <div class="chart-title">Bobot Kriteria</div>
                    <div class="chart-wrapper">
                        <canvas id="chartKriteria"></canvas>
                    </div>
                    <div class="weight-legend">
                        @foreach($kriterias as $index => $k)
                            @php
                                $colors = ['#009688', '#00695C', '#4CAF50', '#FF9800', '#9C27B0', '#2196F3', '#E91E63'];
                                $color = $colors[$index % count($colors)];
                            @endphp
                            <div class="weight-item">
                                <div class="weight-label">
                                    <span class="weight-color" style="background-color: {{ $color }}"></span>
                                    <span>[{{ $k->kode }}] {{ $k->nama }}</span>
                                </div>
                                <span class="weight-value">{{ number_format(($kriteriaWeights[$k->id] ?? 0) * 100, 2) }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Bobot Global Subkriteria --}}
                <div class="chart-box">
                    <div class="chart-title">Bobot Global Subkriteria</div>
                    <div class="chart-wrapper">
                        <canvas id="chartSubkriteria"></canvas>
                    </div>
                    <div class="weight-legend">
                        @foreach($subkriterias as $index => $sk)
                            @php
                                $colors = ['#009688', '#26A69A', '#4DB6AC', '#80CBC4', '#00695C', '#00897B', '#26A69A', '#4CAF50', '#66BB6A', '#81C784', '#FF9800', '#FFA726', '#FFB74D', '#9C27B0', '#AB47BC', '#BA68C8', '#2196F3', '#42A5F5', '#64B5F6', '#E91E63'];
                                $color = $colors[$index % count($colors)];
                            @endphp
                            <div class="weight-item">
                                <div class="weight-label">
                                    <span class="weight-color" style="background-color: {{ $color }}"></span>
                                    <span>[{{ $sk->kriteria->kode }}{{ $sk->kode }}] {{ $sk->nama }}</span>
                                </div>
                                <span class="weight-value">{{ number_format(($globalSubkriteriaWeights[$sk->id] ?? 0) * 100, 2) }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Ranking Table --}}
        <div class="section-label">Peringkat Kelayakan Supplier</div>

        @if($rankings->isEmpty())
            <p style="text-align: center; color: #a0aec0; padding: 24px;">
                Belum ada data pemeringkatan. Lakukan kalkulasi AHP terlebih dahulu.
            </p>
        @else
            <table class="ranking-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">Rank</th>
                        <th style="width: 80px;">Kode</th>
                        <th>Nama Supplier</th>
                        <th>Produk</th>
                        <th class="text-right" style="width: 100px;">Skor Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rankings as $rank)
                        @php
                            $selectedProductIds = \Illuminate\Support\Facades\Cache::get('ahp_selected_products', []);
                            $allProducts = $rank->supplier->produk;
                            if (!empty($selectedProductIds)) {
                                $products = $allProducts->whereIn('id', $selectedProductIds);
                            } else {
                                $products = $allProducts;
                            }
                        @endphp
                        <tr>
                            <td class="text-center">
                                <span class="rank-badge {{ $rank->ranking == 1 ? 'rank-first' : 'rank-other' }}">
                                    {{ $rank->ranking }}
                                </span>
                            </td>
                            <td>{{ $rank->supplier->kode }}</td>
                            <td>
                                <strong>{{ $rank->supplier->nama }}</strong>
                                @if($rank->ranking == 1)
                                    <span class="recommendation-badge">⭐ Rekomendasi Utama</span>
                                @endif
                            </td>
                            <td>
                                @if($products->isEmpty())
                                    <em style="color: #a0aec0;">Belum ada produk</em>
                                @else
                                    <div class="produk-list">
                                        @foreach($products as $product)
                                            <div class="produk-item">
                                                <strong>• {{ $product->nama }}</strong>
                                                @if($product->merk || $product->ukuran)
                                                    <span style="color: #718096;">
                                                        @if($product->merk)
                                                            ({{ $product->merk }}
                                                        @endif
                                                        @if($product->ukuran)
                                                            {{ $product->merk ? ', ' : '(' }}{{ $product->ukuran }}
                                                        @endif
                                                        )
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-right nilai-cell">
                                {{ number_format($rank->nilai_akhir, 5) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>Dokumen ini dibuat secara otomatis oleh Sistem Informasi {{ $companyName }}.</p>
            <p>Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y - HH:mm') }} WIB</p>
        </div>
    </div>

    {{-- Chart.js rendering --}}
    @if($rankings->isNotEmpty() && !empty($kriteriaWeights) && !empty($globalSubkriteriaWeights))
        <script>
            let chartsConverted = false;

            document.addEventListener('DOMContentLoaded', function() {
                // Kriteria Chart
                const kriteriaLabels = @json($kriterias->pluck('nama')->toArray());
                const kriteriaData = @json(array_values($kriteriaWeights));
                const kriteriaColors = ['#009688', '#00695C', '#4CAF50', '#FF9800', '#9C27B0', '#2196F3', '#E91E63'];

                const ctxKriteria = document.getElementById('chartKriteria');
                const chartKriteria = new Chart(ctxKriteria, {
                    type: 'doughnut',
                    data: {
                        labels: kriteriaLabels,
                        datasets: [{
                            data: kriteriaData,
                            backgroundColor: kriteriaColors,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        animation: { duration: 0 },
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        cutout: '65%'
                    }
                });

                // Subkriteria Chart
                const subkriteriaLabels = @json($subkriterias->map(function($sk) {
                    return '[' . $sk->kriteria->kode . $sk->kode . '] ' . $sk->nama;
                })->toArray());
                const subkriteriaData = @json(array_values($globalSubkriteriaWeights));
                const subkriteriaColors = ['#009688', '#26A69A', '#4DB6AC', '#80CBC4', '#00695C', '#00897B', '#26A69A', '#4CAF50', '#66BB6A', '#81C784', '#FF9800', '#FFA726', '#FFB74D', '#9C27B0', '#AB47BC', '#BA68C8', '#2196F3', '#42A5F5', '#64B5F6', '#E91E63'];

                const ctxSubkriteria = document.getElementById('chartSubkriteria');
                const chartSubkriteria = new Chart(ctxSubkriteria, {
                    type: 'doughnut',
                    data: {
                        labels: subkriteriaLabels,
                        datasets: [{
                            data: subkriteriaData,
                            backgroundColor: subkriteriaColors,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        animation: { duration: 0 },
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        cutout: '65%'
                    }
                });

                // Convert charts to images after rendering
                setTimeout(function() {
                    convertChartsToImages();
                }, 500);
            });

            function convertChartsToImages() {
                if (chartsConverted) return;
                
                try {
                    // Convert Kriteria chart
                    const canvasKriteria = document.getElementById('chartKriteria');
                    const imgKriteria = new Image();
                    imgKriteria.src = canvasKriteria.toDataURL('image/png', 1.0);
                    imgKriteria.className = 'chart-image-print';
                    imgKriteria.style.maxWidth = '250px';
                    imgKriteria.style.margin = '0 auto';
                    imgKriteria.style.display = 'none';
                    canvasKriteria.parentNode.insertBefore(imgKriteria, canvasKriteria);
                    
                    // Convert Subkriteria chart
                    const canvasSubkriteria = document.getElementById('chartSubkriteria');
                    const imgSubkriteria = new Image();
                    imgSubkriteria.src = canvasSubkriteria.toDataURL('image/png', 1.0);
                    imgSubkriteria.className = 'chart-image-print';
                    imgSubkriteria.style.maxWidth = '250px';
                    imgSubkriteria.style.margin = '0 auto';
                    imgSubkriteria.style.display = 'none';
                    canvasSubkriteria.parentNode.insertBefore(imgSubkriteria, canvasSubkriteria);
                    
                    chartsConverted = true;
                    
                    // Trigger print after conversion
                    setTimeout(function() {
                        window.print();
                    }, 300);
                } catch (e) {
                    console.error('Error converting charts:', e);
                    // Fallback: just print anyway
                    window.print();
                }
            }

            // Before print event
            window.addEventListener('beforeprint', function() {
                // Hide canvas, show images
                document.querySelectorAll('canvas').forEach(function(canvas) {
                    canvas.style.display = 'none';
                });
                document.querySelectorAll('.chart-image-print').forEach(function(img) {
                    img.style.display = 'block';
                });
            });

            // After print event
            window.addEventListener('afterprint', function() {
                // Show canvas, hide images
                document.querySelectorAll('canvas').forEach(function(canvas) {
                    canvas.style.display = 'block';
                });
                document.querySelectorAll('.chart-image-print').forEach(function(img) {
                    img.style.display = 'none';
                });
            });
        </script>
    @else
        <script>
            // No charts, just print
            window.addEventListener('load', function() {
                window.print();
            });
        </script>
    @endif

</body>
</html>
