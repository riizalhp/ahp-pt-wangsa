<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Penilaian Supplier – {{ $companyName }}</title>
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
        }

        /* Sheet that mimics a paper page on screen */
        .sheet {
            background: #ffffff;
            max-width: 800px;
            margin: 0 auto;
            padding: 32px 40px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            border-radius: 8px;
        }

        /* ── Toolbar (hidden when printing) ─────────────────────── */
        .toolbar {
            max-width: 800px;
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

        /* ── Print rules ────────────────────────────────────────── */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
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

    <div class="sheet">
        {{-- ── Kop Surat ──────────────────────────────────────────── --}}
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

        {{-- ── Meta ───────────────────────────────────────────────── --}}
        <div class="meta-info">
            <span><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</span>
            <span><strong>Jumlah Supplier:</strong> {{ $rankings->count() }}</span>
        </div>

        <div class="section-label">Peringkat Kelayakan Supplier Terpilih</div>

        @if($rankings->isEmpty())
            <p style="color:#718096; font-style:italic; font-size:11px;">
                Data pemeringkatan supplier belum tersedia.
            </p>
        @else
            <table class="ranking-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:50px;">No.</th>
                        <th class="text-center" style="width:70px;">Ranking</th>
                        <th>Nama Supplier</th>
                        <th class="text-right" style="width:130px;">Nilai Akhir (AHP)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rankings as $index => $rank)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">
                                <span class="rank-badge {{ $rank->ranking == 1 ? 'rank-first' : 'rank-other' }}">
                                    {{ $rank->ranking }}
                                </span>
                            </td>
                            <td>
                                {{ $rank->supplier->nama ?? '-' }}
                                @if($rank->ranking == 1)
                                    <span class="recommendation-badge">★ Rekomendasi</span>
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

        <div class="footer">
            Dokumen ini digenerate secara otomatis oleh Sistem Pendukung Keputusan (SPK) &mdash;
            {{ $companyName }} &mdash; {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }}
        </div>
    </div>

    <script>
        // Otomatis buka dialog cetak browser (seperti Ctrl + P) saat halaman dibuka
        window.addEventListener('load', function () {
            window.print();
        });
    </script>

</body>
</html>
