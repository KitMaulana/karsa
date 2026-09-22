<!DOCTYPE html>
<html lang="id">
<body style="font-family: sans-serif; color: #0F3A22; background: #F4F7EE; padding: 24px;">
    <div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 24px; border: 1px solid #DDEBD3;">
        <p style="font-weight: 800; letter-spacing: 0.05em; color: #1D4B2E;">KARSA</p>
        <h2 style="color: #0F3A22;">Laporan Karhutla Diteruskan</h2>
        <p style="color: #5B7263;">Yth. {{ $contact->name }} ({{ $contact->agency }}),</p>
        <p>Laporan warga terverifikasi berikut diteruskan untuk ditindaklanjuti:</p>
        <ul style="color: #0F3A22;">
            <li>Jenis kejadian: {{ ucfirst(str_replace('_', ' ', $report->type)) }}</li>
            <li>Lokasi: {{ $report->lat }}, {{ $report->lng }}</li>
            <li>Waktu laporan: {{ $report->created_at->translatedFormat('d M Y, H.i') }} WIB</li>
            <li>Deskripsi: {{ $report->description }}</li>
        </ul>
        <p style="color: #5B7263; font-size: 13px;">Email ini dikirim otomatis oleh sistem KARSA. Data lokasi pelapor dijaga kerahasiaannya sesuai kebijakan privasi.</p>
    </div>
</body>
</html>
