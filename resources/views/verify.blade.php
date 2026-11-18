<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Verifikasi sertifikat</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #faf9f5; color: #243d35; font: 1rem/1.55 system-ui, sans-serif; }
        main { max-width: 38rem; margin: clamp(2rem, 8vh, 5rem) auto; padding: 0 1.5rem; overflow-wrap: anywhere; }
        h1 { font: normal clamp(2rem, 6vw, 3rem)/1.12 Georgia, serif; }
        dl { border-top: 2px solid #355747; }
        dl div { padding: .85rem 0; border-bottom: 1px solid #c5cec6; }
        dt { color: #526354; font-size: .9rem; }
        dd { margin: .2rem 0 0; font-size: 1.15rem; }
    </style>
</head>
<body>
<main>
    <h1>Verifikasi sertifikat</h1>
    @if ($certificate)
        <dl>
            <div><dt>Status</dt><dd>{{ $certificate->status }}</dd></div>
            <div><dt>Nama peserta</dt><dd>{{ $certificate->participant_name }}</dd></div>
            <div><dt>Kegiatan</dt><dd>{{ $certificate->activity_title }}</dd></div>
            <div><dt>Penerbit</dt><dd>{{ $certificate->issuer }}</dd></div>
            <div><dt>Tanggal terbit</dt><dd>{{ $certificate->issued_date }}</dd></div>
        </dl>
    @else
        <p>Sertifikat tidak ditemukan. Periksa kembali kode pada dokumen Anda.</p>
    @endif
</main>
</body>
</html>
