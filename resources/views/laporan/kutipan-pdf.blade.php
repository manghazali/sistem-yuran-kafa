<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kutipan</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        p.ringkasan { margin: 0 0 4px; color: #4b5563; }
        p.ringkasan strong { color: #16a34a; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background-color: #ecfdf5; font-weight: bold; }
        td.angka { text-align: right; font-weight: bold; color: #16a34a; }
        td.tengah { text-align: center; }
        .tajuk-bar { margin-bottom: 10px; }
        .pecahan { margin: 8px 0 14px; }
        .pecahan span { display: inline-block; margin-right: 16px; }
    </style>
</head>
<body>
    <div class="tajuk-bar">
        <h1>Laporan Kutipan</h1>
        <p class="ringkasan">
            Julat Tarikh: {{ $tarikhMula->format('d/m/Y') }} — {{ $tarikhAkhir->format('d/m/Y') }} |
            Dijana pada {{ now()->translatedFormat('d F Y, h:i A') }} |
            Jumlah Kutipan: <strong>RM{{ number_format($jumlahKeseluruhan, 2) }}</strong>
        </p>
        <div class="pecahan">
            @forelse ($pecahanKaedah as $item)
                <span>{{ $item->kaedah_bayaran }} ({{ $item->bilangan }}): <strong>RM{{ number_format($item->jumlah, 2) }}</strong></span>
            @empty
                <span>Tiada data.</span>
            @endforelse
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">Bil.</th>
                <th style="width: 10%;">No. Bayaran</th>
                <th style="width: 15%;">Pelajar</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 17%;">Keterangan</th>
                <th style="width: 9%;">Amaun (RM)</th>
                <th style="width: 10%;">Kaedah</th>
                <th style="width: 8%;">Tarikh</th>
                <th style="width: 18%;">No. Pesanan Gateway</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bayaran as $item)
                <tr>
                    <td class="tengah">{{ $loop->iteration }}</td>
                    <td>{{ $item->no_bayaran }}</td>
                    <td>{{ $item->pelajar->nama ?? '-' }}</td>
                    <td>{{ $item->pelajar->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td class="angka">{{ number_format($item->amaun, 2) }}</td>
                    <td>{{ $item->kaedah_bayaran }}</td>
                    <td>{{ $item->tarikh_bayaran?->format('d/m/Y') }}</td>
                    <td>{{ $item->no_pesanan_gateway ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="tengah">Tiada bayaran dalam julat tarikh ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
