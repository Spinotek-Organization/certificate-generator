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
        body {
            margin: 0;
            background: #f6f7fb;
            color: #111827;
            font: .94rem/1.55 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .sc-verify-head {
            background: #ffffff;
            color: #111827;
            padding: .65rem 1.25rem;
            border-bottom: 1px solid #eaedf2;
        }
        .sc-verify-head-inner { max-width: 44rem; margin: auto; display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
        .sc-brand-wrap { display: flex; align-items: center; gap: .55rem; }
        .sc-logo-badge {
            width: 1.65rem;
            height: 1.65rem;
            background: transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sc-logo-img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .sc-brand { font-weight: 700; color: #0f172a; letter-spacing: -.015em; font-size: .95rem; }
        .sc-head-sub { color: #6b7280; font-size: .84rem; font-weight: 500; }

        main { max-width: 44rem; margin: clamp(1.5rem, 5vh, 3.25rem) auto; padding: 0 1.25rem; overflow-wrap: anywhere; }
        .certificate {
            padding: clamp(1.35rem, 4.5vw, 2rem);
            border: 1px solid #eaedf2;
            border-radius: .75rem;
            background: #ffffff;
            box-shadow: 0 1px 2px rgb(16 24 40 / 3%);
        }
        h1 { margin: 0 0 .35rem; font-size: clamp(1.4rem, 4vw, 1.75rem); font-weight: 700; color: #0f172a; letter-spacing: -.02em; }
        .subtitle { margin: 0 0 1.4rem; color: #6b7280; font-size: .9rem; }
        .search-form { display: flex; gap: .55rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .search-form input {
            flex: 1 1 15rem;
            padding: .58rem .85rem;
            border: 1px solid #e2e6ee;
            border-radius: .5rem;
            font: inherit;
            font-size: .9rem;
            background: #ffffff;
            color: #111827;
        }
        .search-form input:focus { outline: none; border-color: #0066ff; box-shadow: 0 0 0 3px rgb(0 102 255 / 12%); }
        .search-form button {
            padding: .58rem 1.25rem;
            border: 0;
            border-radius: .5rem;
            background: #0066ff;
            color: #ffffff;
            font: inherit;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 120ms ease;
        }
        .search-form button:hover { background: #0052e0; }
        dl { margin: 0; }
        dl div { padding: .85rem 0; border-top: 1px solid #f0f2f6; }
        dt { color: #6b7280; font-size: .8rem; font-weight: 600; }
        dd { margin: .18rem 0 0; font-size: 1rem; color: #111827; }
        .recipient dd { font-size: clamp(1.3rem, 3.8vw, 1.68rem); font-weight: 700; color: #0f172a; letter-spacing: -.015em; }
        .badge { display: inline-block; padding: .15rem .62rem; border-radius: 999px; font-size: .79rem; font-weight: 600; }
        .status-valid { color: #047857; background: #ecfdf5; }
        .status-revoked { color: #b91c1c; background: #fef2f2; }
        .category-pill { color: #0052cc; background: #eff6ff; margin-left: .35rem; }
        .not-found { padding: .95rem 1.1rem; border-radius: .55rem; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: .89rem; }
        code { background: #f3f5f8; color: #1f2937; padding: .14rem .42rem; border-radius: .3rem; font-size: .86rem; }
    </style>
</head>
<body>
<header class="sc-verify-head">
    <div class="sc-verify-head-inner">
        <div class="sc-brand-wrap">
            @include('spinotek-certificates::partials.brand-logo')
            <span class="sc-brand">Spinotek</span>
        </div>
        <span class="sc-head-sub">Certificate Verification</span>
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
