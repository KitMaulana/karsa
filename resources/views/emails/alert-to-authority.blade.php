<!DOCTYPE html>
<html lang="id">
<body style="font-family: sans-serif; color: #0F3A22; background: #F4F7EE; padding: 24px;">
    <div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 24px; border: 1px solid #DDEBD3;">
        <p style="font-weight: 800; letter-spacing: 0.05em; color: #1D4B2E;">KARSA</p>
        <h2 style="color: #0F3A22;">Peringatan Karhutla: {{ $districtName }}</h2>
        <p style="color: #5B7263;">Yth. {{ $contact->name }} ({{ $contact->agency }}),</p>
        <p>{{ $alertMessage }}</p>
        <p style="color: #5B7263; font-size: 13px;">Email ini dikirim otomatis oleh sistem KARSA (Kawasan Analisis Risiko dan Siaga). Untuk keadaan darurat, mohon koordinasikan langsung dengan pihak terkait di lapangan.</p>
    </div>
</body>
</html>
