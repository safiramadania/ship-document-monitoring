<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document->documentType?->name ?? 'Dokumen Kapal' }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6;">
    <h1 style="font-size: 18px; margin-bottom: 16px;">Reminder Dokumen Kapal</h1>
    <p>Berikut reminder dokumen kapal dari Ship Document Monitoring.</p>
    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 720px;">
        <tr>
            <td style="border: 1px solid #e2e8f0; font-weight: bold;">Cabang</td>
            <td style="border: 1px solid #e2e8f0;">{{ $document->vessel?->branch?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; font-weight: bold;">Kapal</td>
            <td style="border: 1px solid #e2e8f0;">{{ $document->vessel?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; font-weight: bold;">Jenis Dokumen</td>
            <td style="border: 1px solid #e2e8f0;">{{ $document->documentType?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; font-weight: bold;">Nomor Surat</td>
            <td style="border: 1px solid #e2e8f0;">{{ $document->letter_number ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; font-weight: bold;">Tanggal Expired</td>
            <td style="border: 1px solid #e2e8f0;">{{ $document->expires_at?->format('Y-m-d') ?? '-' }}</td>
        </tr>
    </table>
    <pre style="margin-top: 16px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; white-space: pre-wrap;">{{ $bodyText }}</pre>
</body>
</html>
