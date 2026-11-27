@php
    $clientName = $clientName ?? config('app.name', 'Portal Sertifikat');
    $clientTagline = $clientTagline ?? 'Portal Verifikasi Sertifikat Resmi';
    $clientLogoUrl = $clientLogoUrl ?? null;
    $clientFaviconUrl = $clientFaviconUrl ?? $clientLogoUrl;
    $clientWebsiteUrl = $clientWebsiteUrl ?? url('/');
    $clientInitial = strtoupper(mb_substr(trim((string) $clientName), 0, 1) ?: 'S');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Verifikasi Sertifikat — {{ $clientName }}</title>
    @if ($clientFaviconUrl)
        <link rel="icon" href="{{ $clientFaviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%230066ff'/%3E%3Cpath d='M9.5 16.5l4.2 4.2 8.8-8.9' fill='none' stroke='%23ffffff' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">
    @endif
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f6f7fb;
            color: #111827;
            font: .92rem/1.55 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; }

        /* Client Top Bar */
        .sc-topbar {
            background: #ffffff;
            border-bottom: 1px solid #eaedf2;
            padding: .7rem 1.25rem;
        }
        .sc-topbar-inner {
            max-width: 56rem;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .sc-brand-wrap { display: flex; align-items: center; gap: .65rem; min-width: 0; text-decoration: none; }
        .sc-client-logo { height: 1.9rem; width: auto; max-width: 8rem; object-fit: contain; display: block; }
        .sc-client-monogram {
            width: 1.9rem;
            height: 1.9rem;
            border-radius: .5rem;
            background: #0066ff;
            color: #ffffff;
            font-weight: 700;
            font-size: .92rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sc-client-name { font-weight: 700; color: #0f172a; font-size: .96rem; letter-spacing: -.015em; }
        .sc-client-sub {
            font-size: .76rem;
            font-weight: 600;
            color: #0052cc;
            background: #eff6ff;
            padding: .16rem .6rem;
            border-radius: 999px;
        }
        .sc-back-link {
            font-size: .83rem;
            font-weight: 550;
            color: #4b5563;
            text-decoration: none;
            padding: .38rem .75rem;
            border: 1px solid #e2e6ee;
            border-radius: .5rem;
            background: #ffffff;
            transition: background-color 120ms ease, border-color 120ms ease, color 120ms ease;
        }
        .sc-back-link:hover { background: #f8fafc; border-color: #cbd5e1; color: #0f172a; }

        /* Main Portal Layout */
        main {
            max-width: 56rem;
            margin: clamp(1.5rem, 4.5vh, 2.75rem) auto 3.5rem;
            padding: 0 1.25rem;
            display: grid;
            gap: 1.25rem;
            overflow-wrap: anywhere;
        }
        .sc-card {
            background: #ffffff;
            border: 1px solid #eaedf2;
            border-radius: .85rem;
            box-shadow: 0 1px 2px rgb(16 24 40 / 3%);
        }

        /* Search Header Card */
        .sc-search-card { padding: clamp(1.35rem, 3.5vw, 2rem); }
        .sc-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .75rem;
            font-weight: 650;
            color: #0052cc;
            background: #eff6ff;
            padding: .2rem .65rem;
            border-radius: 999px;
            margin-bottom: .75rem;
        }
        h1 { margin: 0 0 .35rem; font-size: clamp(1.45rem, 3.6vw, 1.85rem); font-weight: 700; color: #0f172a; letter-spacing: -.02em; }
        .subtitle { margin: 0 0 1.35rem; color: #6b7280; font-size: .92rem; max-width: 38rem; }

        .search-form { display: flex; gap: .6rem; flex-wrap: wrap; }
        .search-input-wrap { position: relative; flex: 1 1 17rem; }
        .search-icon {
            position: absolute;
            left: .85rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1rem;
            height: 1rem;
            color: #9ca3af;
            pointer-events: none;
        }
        .search-form input {
            width: 100%;
            padding: .68rem .95rem .68rem 2.45rem;
            border: 1px solid #dce2ec;
            border-radius: .55rem;
            font: inherit;
            font-size: .92rem;
            background: #ffffff;
            color: #111827;
        }
        .search-form input:focus { outline: none; border-color: #0066ff; box-shadow: 0 0 0 3px rgb(0 102 255 / 14%); }
        .search-form button {
            padding: .68rem 1.35rem;
            border: 0;
            border-radius: .55rem;
            background: #0066ff;
            color: #ffffff;
            font: inherit;
            font-size: .92rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 120ms ease;
        }
        .search-form button:hover { background: #0052e0; }

        /* Verification Result Card */
        .sc-result-card { overflow: hidden; }
        .sc-verify-banner {
            padding: 1.05rem clamp(1.25rem, 3.5vw, 1.85rem);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            border-bottom: 1px solid #eaedf2;
        }
        .sc-verify-banner.is-valid { background: #ecfdf5; border-bottom-color: #d1fae5; }
        .sc-verify-banner.is-revoked { background: #fef2f2; border-bottom-color: #fee2e2; }
        .sc-banner-left { display: flex; align-items: center; gap: .8rem; }
        .sc-shield {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: .65rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .is-valid .sc-shield { background: #10b981; color: #ffffff; }
        .is-revoked .sc-shield { background: #ef4444; color: #ffffff; }
        .sc-shield svg { width: 1.25rem; height: 1.25rem; stroke: currentColor; stroke-width: 2.2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
        .sc-banner-title { font-weight: 700; font-size: .98rem; color: #0f172a; }
        .sc-banner-sub { font-size: .81rem; color: #4b5563; }

        .sc-badges { display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; }
        .badge { display: inline-block; padding: .2rem .68rem; border-radius: 999px; font-size: .78rem; font-weight: 650; }
        .status-valid { color: #065f46; background: #d1fae5; }
        .status-revoked { color: #991b1b; background: #fee2e2; }
        .category-pill { color: #0052cc; background: #eff6ff; border: 1px solid #dbeafe; }

        .sc-result-body { padding: clamp(1.35rem, 3.5vw, 1.85rem); display: grid; gap: 1.4rem; }
        .sc-recipient-box {
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #f0f2f6;
        }
        .sc-label { font-size: .76rem; font-weight: 650; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
        .sc-recipient-name { margin: .25rem 0 .45rem; font-size: clamp(1.45rem, 3.8vw, 1.9rem); font-weight: 750; color: #0f172a; letter-spacing: -.02em; line-height: 1.2; }
        .sc-activity { font-size: 1.02rem; font-weight: 550; color: #374151; }

        .sc-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(13.5rem, 1fr));
            gap: 1rem 1.25rem;
        }
        .sc-meta-item { display: grid; gap: .2rem; align-content: start; }
        .sc-meta-val { font-size: .94rem; font-weight: 550; color: #111827; }
        code { background: #f3f5f8; color: #0f172a; padding: .16rem .45rem; border-radius: .35rem; font-size: .84rem; font-weight: 600; display: inline-block; }

        .sc-revoke-note {
            padding: .85rem 1rem;
            border-radius: .55rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: .88rem;
        }
        .not-found {
            padding: 1.15rem 1.35rem;
            border-radius: .75rem;
            background: #ffffff;
            border: 1px solid #fecaca;
            color: #991b1b;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        /* Guide Cards on initial state */
        .sc-guide-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
            gap: .95rem;
        }
        .sc-guide-card {
            padding: 1.15rem 1.2rem;
            display: grid;
            gap: .4rem;
            align-content: start;
        }
        .sc-guide-num {
            width: 1.65rem;
            height: 1.65rem;
            border-radius: .45rem;
            background: #eff6ff;
            color: #0066ff;
            font-size: .78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .15rem;
        }
        .sc-guide-title { font-size: .9rem; font-weight: 650; color: #0f172a; }
        .sc-guide-desc { font-size: .82rem; color: #6b7280; line-height: 1.48; }

        .sc-foot {
            text-align: center;
            color: #6b7280;
            font-size: .79rem;
            padding-top: .25rem;
        }
    </style>
</head>
<body>
<header class="sc-topbar">
    <div class="sc-topbar-inner">
        <a href="{{ route('spinotek.certificates.verify.index') }}" class="sc-brand-wrap">
            @if ($clientLogoUrl)
                <img src="{{ $clientLogoUrl }}" alt="{{ $clientName }}" class="sc-client-logo">
            @else
                <span class="sc-client-monogram" aria-hidden="true">{{ $clientInitial }}</span>
            @endif
            <span class="sc-client-name">{{ $clientName }}</span>
            <span class="sc-client-sub">Verifikasi Resmi</span>
        </a>
        <a href="{{ $clientWebsiteUrl }}" class="sc-back-link">← Kembali ke {{ $clientName }}</a>
    </div>
</header>

<main>
    <section class="sc-card sc-search-card">
        <span class="sc-eyebrow">{{ $clientTagline }}</span>
        <h1>Verifikasi Keaslian Sertifikat</h1>
        <p class="subtitle">Pastikan keabsahan sertifikat yang diterbitkan oleh <strong>{{ $clientName }}</strong> dengan memasukkan nomor sertifikat atau memindai QR Code pada dokumen.</p>

        <form method="GET" action="{{ route('spinotek.certificates.verify.index') }}" class="search-form">
            <div class="search-input-wrap">
                <svg class="search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="9" cy="9" r="6"/>
                    <path d="M13.5 13.5L17 17" stroke-linecap="round"/>
                </svg>
                <input type="text" name="q" value="{{ $query }}" placeholder="Masukkan nomor sertifikat (mis. CERT/EVT/2026/00001) atau kode UUID" aria-label="Nomor atau kode sertifikat" required>
            </div>
            <button type="submit">Cek Sertifikat</button>
        </form>
    </section>

    @if ($searched && $certificate)
        <section class="sc-card sc-result-card">
            <div @class(['sc-verify-banner', 'is-valid' => $certificate->isValid(), 'is-revoked' => $certificate->isRevoked()])>
                <div class="sc-banner-left">
                    <span class="sc-shield" aria-hidden="true">
                        @if ($certificate->isValid())
                            <svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3z"/><polyline points="9 12 11.2 14.2 15.5 9.8"/></svg>
                        @else
                            <svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3z"/><line x1="9.5" y1="9.5" x2="14.5" y2="14.5"/><line x1="14.5" y1="9.5" x2="9.5" y2="14.5"/></svg>
                        @endif
                    </span>
                    <div>
                        <div class="sc-banner-title">
                            {{ $certificate->isValid() ? 'Sertifikat Terverifikasi & Sah' : 'Sertifikat Telah Dicabut' }}
                        </div>
                        <div class="sc-banner-sub">
                            Tercatat resmi pada sistem verifikasi {{ $clientName }}
                        </div>
                    </div>
                </div>
                <div class="sc-badges">
                    <span @class(['badge', 'status-valid' => $certificate->isValid(), 'status-revoked' => $certificate->isRevoked()])>
                        {{ $certificate->status }}
                    </span>
                    <span class="badge category-pill">{{ $certificate->categoryLabel() }}</span>
                </div>
            </div>

            <div class="sc-result-body">
                <div class="sc-recipient-box">
                    <div class="sc-label">Diberikan Kepada</div>
                    <div class="sc-recipient-name">{{ $certificate->participant_name }}</div>
                    <div class="sc-activity">{{ $certificate->activity_title }}</div>
                </div>

                <div class="sc-meta-grid">
                    <div class="sc-meta-item">
                        <span class="sc-label">Nomor Sertifikat</span>
                        <span class="sc-meta-val"><code>{{ $certificate->certificate_number }}</code></span>
                    </div>
                    <div class="sc-meta-item">
                        <span class="sc-label">Penerbit</span>
                        <span class="sc-meta-val">{{ $certificate->issuer }}</span>
                    </div>
                    <div class="sc-meta-item">
                        <span class="sc-label">Tanggal Terbit</span>
                        <span class="sc-meta-val">{{ $certificate->issued_date?->format('d M Y') ?? $certificate->issued_date }}</span>
                    </div>
                    <div class="sc-meta-item">
                        <span class="sc-label">Kode Verifikasi</span>
                        <span class="sc-meta-val"><code>{{ $certificate->code }}</code></span>
                    </div>
                    @foreach ($publicVariableLabels as $key => $label)
                        @if (! empty($certificate->variables[$key]))
                            <div class="sc-meta-item">
                                <span class="sc-label">{{ $label }}</span>
                                <span class="sc-meta-val">{{ $certificate->variables[$key] }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if ($certificate->isRevoked() && $certificate->revoked_reason)
                    <div class="sc-revoke-note">
                        <strong>Alasan Pencabutan:</strong> {{ $certificate->revoked_reason }}
                    </div>
                @endif
            </div>
        </section>
    @elseif ($searched)
        <section class="not-found">
            <div>
                Sertifikat dengan nomor/kode <strong>{{ $query }}</strong> tidak ditemukan. Periksa kembali nomor yang tertera pada dokumen Anda.
            </div>
        </section>
    @else
        <section class="sc-guide-grid" aria-label="Cara verifikasi">
            <article class="sc-card sc-guide-card">
                <span class="sc-guide-num">1</span>
                <div class="sc-guide-title">Masukkan Nomor Sertifikat</div>
                <div class="sc-guide-desc">Ketik nomor resmi yang tercetak pada dokumen sertifikat atau kode UUID uniknya.</div>
            </article>
            <article class="sc-card sc-guide-card">
                <span class="sc-guide-num">2</span>
                <div class="sc-guide-title">Atau Pindai QR Code</div>
                <div class="sc-guide-desc">Arahkan kamera ponsel ke QR Code pada sertifikat untuk membuka halaman validasi secara otomatis.</div>
            </article>
            <article class="sc-card sc-guide-card">
                <span class="sc-guide-num">3</span>
                <div class="sc-guide-title">Cocokkan Data Penerima</div>
                <div class="sc-guide-desc">Pastikan nama peserta, judul program, tanggal terbit, dan status sertifikat sesuai dengan dokumen fisik/PDF.</div>
            </article>
        </section>
    @endif

    <footer class="sc-foot">
        Verifikasi sertifikat digital resmi &copy; {{ date('Y') }} {{ $clientName }}
    </footer>
</main>
</body>
</html>
