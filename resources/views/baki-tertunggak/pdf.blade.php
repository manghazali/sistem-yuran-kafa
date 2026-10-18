<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Baki Belum Dibayar</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        p.ringkasan { margin: 0 0 14px; color: #4b5563; }
        p.ringkasan strong { color: #dc2626; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 8px; text-align: left; vertical-align: top; }
        th { background-color: #ecfdf5; font-weight: bold; }
        td.angka { text-align: right; font-weight: bold; color: #dc2626; }
        td.tengah { text-align: center; }
        .badge { display: inline-block; background-color: #fee2e2; color: #991b1b; border-radius: 8px; padding: 1px 6px; margin: 1px; font-size: 9px; }
        .tajuk-bar { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="tajuk-bar">
        <h1>Laporan Baki Belum Dibayar</h1>
        <p class="ringkasan">
            Dijana pada {{ now()->translatedFormat('d F Y, h:i A') }} —
            Jumlah Keseluruhan Belum Dibayar: <strong>RM{{ number_format($jumlahKeseluruhanBelumDibayar, 2) }}</strong>
            ({{ $mengikutPelajar->count() }} pelajar)
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">Bil.</th>
                <th style="width: 20%;">Pelajar</th>
                <th style="width: 12%;">Kelas</th>
                <th style="width: 40%;">Yuran Belum Bayar</th>
                <th style="width: 12%;">Bil. Bulan Belum Dibayar</th>
                <th style="width: 13%;">Jumlah Baki (RM)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mengikutPelajar as $data)
                <tr>
                    <td class="tengah">{{ $loop->iteration }}</td>
                    <td>{{ $data['pelajar']->nama ?? '-' }}</td>
                    <td>{{ $data['pelajar']->kelas->nama_kelas ?? '-' }}</td>
                    <td>
                        @foreach ($data['senarai_yuran'] as $yuran)
                            <span class="badge">{{ $yuran['nama'] }} (RM{{ number_format($yuran['baki'], 2) }})</span>
                        @endforeach
                    </td>
                    <td class="tengah">{{ $data['bilangan_bulan'] }}</td>
                    <td class="angka">{{ number_format($data['jumlah_baki'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="tengah">Tiada baki belum dibayar. Semua yuran telah dibayar.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
