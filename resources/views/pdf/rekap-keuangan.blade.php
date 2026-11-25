{{-- Rekap keuangan PDF (FR-38): ringkasan per status + per peminatan sesuai filter HW-32. Gaya di resources/css/pdf.css. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Keuangan</title>
    <style>{!! file_get_contents(resource_path('css/pdf.css')) !!}</style>
</head>
<body>
    <div class="kepala">
        <div class="merek">Polibatam Verify</div>
        <div class="sub">Rekap keuangan · dicetak {{ $dicetak }}</div>
    </div>
    <table>
        @foreach (['Lunas' => 'lunas', 'Menunggu verifikasi' => 'menunggu', 'Belum bayar' => 'belum', 'Tunggakan' => 'tunggakan'] as $label => $k)
            <tr><td class="label">{{ $label }}</td><td class="nilai">{{ $ringkasan[$k]['nominal'] }} ({{ $ringkasan[$k]['jumlah'] }} tagihan)</td></tr>
        @endforeach
    </table>
    <h1 style="margin-top: 18px">Per peminatan</h1>
    <table>
        <tr><td class="label">Peminatan</td><td class="label">Lunas</td><td class="label">Nominal lunas</td><td class="label">Belum bayar</td><td class="label">Tunggakan</td></tr>
        @foreach ($baris as $b)
            <tr><td>{{ $b['peminatan'] }}</td><td>{{ $b['lunas'] }}</td><td>{{ $b['nominal_lunas'] }}</td><td>{{ $b['belum'] }}</td><td>{{ $b['tunggakan'] }}</td></tr>
        @endforeach
    </table>
    <p class="catatan">Tunggakan = tagihan Belum Bayar/Ditolak yang lewat jatuh tempo. Nominal termasuk kode unik.</p>
</body>
</html>
