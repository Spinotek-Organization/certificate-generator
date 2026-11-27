<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Verifikasi Sertifikat — Spinotek</title>
    @if ($logoDataUri = \Spinotek\CertificateGenerator\CertificateServiceProvider::logoDataUri())
        <link rel="icon" type="image/png" href="{{ $logoDataUri }}">
    @endif
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f2f6fc; color: #0f1f38; font: 1rem/1.55 system-ui, -apple-system, sans-serif; }
        .sc-verify-head { background: linear-gradient(135deg, #07142b 0%, #0d244d 58%, #0a3b7a 100%); color: #f4f8ff; padding: .95rem 1.25rem; box-shadow: 0 4px 18px rgb(7 20 43 / 15%); }
        .sc-verify-head-inner { max-width: 44rem; margin: auto; display: flex; align-items: center; gap: .7rem; }
        .sc-logo-badge { width: 2.1rem; height: 2.1rem; border-radius: .55rem; background: #ffffff; display: inline-flex; align-items: center; justify-content: center; padding: .2rem; box-shadow: 0 2px 10px rgb(0 150 255 / 28%); flex-shrink: 0; }
        .sc-logo-img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .sc-brand { font-weight: 700; color: #7cd4ff; letter-spacing: .01em; }
        main { max-width: 44rem; margin: clamp(1.5rem, 5vh, 3.5rem) auto; padding: 0 1rem; overflow-wrap: anywhere; }
        .certificate { padding: clamp(1.25rem, 5vw, 2.35rem); border: 1px solid #dbe5f3; border-top: .28rem solid #0066ff; border-radius: .75rem; background: #ffffff; box-shadow: 0 1rem 2.75rem rgb(11 36 77 / 8%); }
        h1 { margin: 0 0 .45rem; font-size: clamp(1.55rem, 4.5vw, 2.1rem); font-weight: 750; color: #0b254b; letter-spacing: -.015em; }
        .subtitle { margin: 0 0 1.3rem; color: #526582; font-size: .94rem; }
        .search-form { display: flex; gap: .6rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .search-form input { flex: 1 1 15rem; padding: .65rem .9rem; border: 1px solid #c4d5eb; border-radius: .5rem; font: inherit; background: #fbfdff; color: #0f1f38; }
        .search-form input:focus { outline: 2px solid #0066ff; border-color: #0066ff; background: #ffffff; }
        .search-form button { padding: .65rem 1.3rem; border: 0; border-radius: .5rem; background: linear-gradient(135deg, #0088ff, #0052e0); color: #ffffff; font: inherit; font-weight: 600; cursor: pointer; box-shadow: 0 2px 10px rgb(0 92 230 / 24%); }
        .search-form button:hover { background: linear-gradient(135deg, #0077eb, #0046c7); }
        dl { margin: 0; }
        dl div { padding: .85rem 0; border-top: 1px solid #e6eef9; }
        dt { color: #526582; font-size: .86rem; font-weight: 600; }
        dd { margin: .18rem 0 0; font-size: 1.06rem; color: #0f1f38; }
        .recipient dd { font-size: clamp(1.35rem, 4vw, 1.85rem); font-weight: 700; color: #0b254b; }
        .badge { display: inline-block; padding: .18rem .72rem; border-radius: 999px; font-size: .86rem; font-weight: 650; }
        .status-valid { color: #0c6b43; background: #dff5ec; }
        .status-revoked { color: #b42318; background: #fee4e2; }
        .category-pill { color: #004ec2; background: #eaf2ff; margin-left: .4rem; }
        .not-found { padding: 1rem 1.1rem; border-radius: .5rem; background: #fff4f2; border: 1px solid #f3c4be; color: #b42318; }
        code { background: #eef4fc; color: #0b3a78; padding: .15rem .45rem; border-radius: .3rem; font-size: .92rem; }
    </style>
</head>
<body>
<header class="sc-verify-head">
    <div class="sc-verify-head-inner">
        @include('spinotek-certificates::partials.brand-logo')
        <span class="sc-brand">Spinotek</span>
        <span style="opacity:.75;font-size:.9rem;">Certificate Verification</span>
    </div>
</header>
<main>
    <div class="certificate">
        <h1>Verifikasi Sertifikat</h1>
        <p class="subtitle">Masukkan nomor sertifikat atau pindai QR Code pada dokumen untuk memvalidasi keasliannya.</p>

        <form method="GET" action="{{ route('spinotek.certificates.verify.index') }}" class="search-form">
            <input type="text" name="q" value="{{ $query }}" placeholder="Contoh: CERT/EVT/2026/00001 atau kode UUID" aria-label="Nomor atau kode sertifikat" required>
            <button type="submit">Cek Sertifikat</button>
        </form>

        @if ($searched && $certificate)
            <dl>
                <div>
                    <dt>Status &amp; Kategori</dt>
                    <dd>
                        <span @class(['badge', 'status-valid' => $certificate->isValid(), 'status-revoked' => $certificate->isRevoked()])>
                            {{ $certificate->status }}
                        </span>
                        <span class="badge category-pill">{{ $certificate->categoryLabel() }}</span>
                    </dd>
                </div>
                <div>
                    <dt>Nomor Sertifikat</dt>
                    <dd><code>{{ $certificate->certificate_number }}</code></dd>
                </div>
                <div class="recipient">
                    <dt>Nama Peserta</dt>
                    <dd>{{ $certificate->participant_name }}</dd>
                </div>
                <div>
                    <dt>Kegiatan / Program</dt>
                    <dd>{{ $certificate->activity_title }}</dd>
                </div>
                <div>
                    <dt>Penerbit</dt>
                    <dd>{{ $certificate->issuer }}</dd>
                </div>
                <div>
                    <dt>Tanggal Terbit</dt>
                    <dd>{{ $certificate->issued_date?->format('d M Y') ?? $certificate->issued_date }}</dd>
                </div>
                @if ($certificate->isRevoked() && $certificate->revoked_reason)
                    <div>
                        <dt>Alasan Pencabutan</dt>
                        <dd>{{ $certificate->revoked_reason }}</dd>
                    </div>
                @endif
                @foreach ($publicVariableLabels as $key => $label)
                    @if (! empty($certificate->variables[$key]))
                        <div>
                            <dt>{{ $label }}</dt>
                            <dd>{{ $certificate->variables[$key] }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        @elseif ($searched)
            <div class="not-found">
                Sertifikat dengan nomor/kode <strong>{{ $query }}</strong> tidak ditemukan. Periksa kembali nomor yang tertera pada dokumen Anda.
            </div>
        @endif
    </div>
</main>
</body>
</html>
