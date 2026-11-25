{{-- Kartu pendaftar PDF (BR-73, FR-68, A-63): dibangkitkan saat diunduh, tanpa QR. Tidak memuat NIK maupun data rekening.
     Gaya di resources/css/pdf.css (dompdf tidak membaca token Tailwind). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Pendaftar {{ $kartu['nomor'] }}</title>
    <style>{!! file_get_contents(resource_path('css/pdf.css')) !!}</style>
</head>
<body>
    <div class="kepala">
        <div class="merek">Polibatam Verify</div>
        <div class="sub">Program Microcredential · Politeknik Negeri Batam</div>
    </div>

    <h1>Kartu Pendaftar</h1>
    <div class="sub">Status terkini saat kartu diunduh.</div>
    <div class="nomor">{{ $kartu['nomor'] }}</div>

    <table>
        <tr><td class="label">Nama lengkap</td><td class="nilai">{{ $kartu['nama'] }}</td></tr>
        <tr><td class="label">Track</td><td class="nilai">{{ $kartu['track'] }}</td></tr>
        <tr><td class="label">Peminatan</td><td class="nilai">{{ $kartu['peminatan'] }}</td></tr>
        <tr><td class="label">Periode pendaftaran</td><td class="nilai">{{ $kartu['periode'] }}</td></tr>
        <tr><td class="label">Waktu pengajuan</td><td class="nilai">{{ $diajukan }}</td></tr>
        <tr><td class="label">Status terkini</td><td class="nilai">{{ $kartu['status'] }}</td></tr>
    </table>

    <p class="catatan">Dicetak {{ $dicetak }}. Kartu ini bukan bukti kelulusan maupun bukti pembayaran.
        Status terbaru selalu dapat dipantau di portal Polibatam Verify.</p>
</body>
</html>
