<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Verifikasi sertifikat</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f4efe5; color: #14233c; font: 1rem/1.55 system-ui, sans-serif; }
        main { max-width: 42rem; margin: clamp(1.5rem, 8vh, 5rem) auto; padding: 0 1rem; overflow-wrap: anywhere; }
        .certificate { padding: clamp(1.25rem, 5vw, 2.5rem); border: 1px solid #d9cebc; border-top: .22rem solid #a27d34; border-radius: .35rem; background: #fffdfa; box-shadow: 0 1rem 3rem rgb(20 35 60 / 8%); }
        h1 { margin: 0 0 1.5rem; font: normal clamp(1.9rem, 6vw, 2.75rem)/1.12 Georgia, serif; }
        dl { margin: 0; }
        dl div { padding: .85rem 0; border-top: 1px solid #e5ddcf; }
        dt { color: #4a586c; font-size: .9rem; }
        dd { margin: .15rem 0 0; font-size: 1.1rem; }
        .recipient dd { font: normal clamp(1.6rem, 5vw, 2.2rem)/1.2 Georgia, serif; }
        .status { display: inline-block; padding: .12rem .65rem; border-radius: 2rem; color: #38465a; background: #eeeae2; font-size: .95rem; font-weight: 650; }
        .status-valid { color: #174a37; background: #e3efe6; }
        p { margin: 0; }
    </style>
</head>
<body>
<main><div class="certificate">
    <h1>Verifikasi sertifikat</h1>
    @if ($certificate)
        <dl>
            <div><dt>Status</dt><dd><span @class(['status', 'status-valid' => $certificate->status === 'Valid'])>{{ $certificate->status }}</span></dd></div>
            <div class="recipient"><dt>Nama peserta</dt><dd>{{ $certificate->participant_name }}</dd></div>
            <div><dt>Kegiatan</dt><dd>{{ $certificate->activity_title }}</dd></div>
            <div><dt>Penerbit</dt><dd>{{ $certificate->issuer }}</dd></div>
            <div><dt>Tanggal terbit</dt><dd>{{ $certificate->issued_date }}</dd></div>
        </dl>
    @else
        <p>Sertifikat tidak ditemukan. Periksa kembali kode pada dokumen Anda.</p>
    @endif
</div></main>
</body>
</html>
