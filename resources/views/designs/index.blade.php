<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Certificate Creator — Template Sertifikat</title>
    @if ($logoDataUri = \Spinotek\CertificateGenerator\CertificateServiceProvider::logoDataUri())
        <link rel="icon" type="image/png" href="{{ $logoDataUri }}">
    @endif
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f7f7f5;
            color: #111827;
            font: .9rem/1.5 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .sc-head {
            background: #ffffff;
            color: #111827;
            border-bottom: 1px solid rgb(15 23 42 / 6%);
        }
        .sc-head-inner {
            max-width: 78rem;
            margin: auto;
            padding: .45rem 1.5rem;
            min-height: 2.95rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .85rem;
            flex-wrap: wrap;
        }
        .sc-title { display: flex; align-items: center; gap: .55rem; }
        .sc-logo-badge {
            width: 1.6rem;
            height: 1.6rem;
            border-radius: .38rem;
            background: transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            flex-shrink: 0;
        }
        .sc-logo-img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .sc-brand {
            color: #6b7280;
            font-weight: 600;
            padding-right: .65rem;
            border-right: 1px solid rgb(15 23 42 / 8%);
            font-size: .86rem;
        }
        h1 { margin: 0; font-size: 1.02rem; font-weight: 650; letter-spacing: -.015em; color: #111827; }
        .sc-container { max-width: 78rem; margin: 1.75rem auto 3rem; padding: 0 1.5rem; display: grid; gap: 1.5rem; }
        .sc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            border: 1px solid transparent;
            border-radius: .45rem;
            background: #f3f4f6;
            color: #374151;
            padding: .48rem .9rem;
            font: inherit;
            font-size: .84rem;
            font-weight: 550;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 120ms ease, color 120ms ease, box-shadow 120ms ease;
        }
        .sc-btn:hover { background: #e5e7eb; color: #111827; }
        .sc-btn-primary { background: #0066ff; border-color: #0066ff; color: #ffffff; font-weight: 600; }
        .sc-btn-primary:hover { background: #0052e0; border-color: #0052e0; color: #ffffff; }
        .sc-btn-ghost { background: transparent; color: #4b5563; }
        .sc-btn-ghost:hover { background: #f3f4f6; color: #111827; }
        .sc-btn-sky { background: #f3f4f6; color: #111827; }
        .sc-btn-sky:hover { background: #e5e7eb; }
        .sc-btn-danger { background: transparent; color: #dc2626; }
        .sc-btn-danger:hover { background: #fef2f2; color: #b91c1c; }
        .sc-card {
            background: #ffffff;
            border: 0;
            border-radius: .75rem;
            padding: 1.35rem;
            box-shadow: 0 1px 2px rgb(15 23 42 / 4%), 0 0 0 1px rgb(15 23 42 / 5%);
        }
        .sc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(19.5rem, 1fr)); gap: 1rem; }
        .sc-tpl { display: grid; gap: .9rem; align-content: space-between; transition: box-shadow 140ms ease, transform 140ms ease; }
        .sc-tpl:hover { box-shadow: 0 8px 24px rgb(15 23 42 / 6%), 0 0 0 1px rgb(0 102 255 / 22%); }
        .sc-badges { display: flex; flex-wrap: wrap; gap: .38rem; }
        .sc-badge { font-size: .74rem; font-weight: 600; padding: .15rem .55rem; border-radius: 999px; background: #eff6ff; color: #0052cc; }
        .sc-badge-default { background: #ecfdf5; color: #047857; }
        .sc-meta { color: #6b7280; font-size: .81rem; display: grid; gap: .26rem; }
        .sc-meta code { background: #f3f4f6; color: #1f2937; padding: .1rem .36rem; border-radius: .28rem; font-size: .77rem; }
        .sc-tpl-actions { display: flex; flex-wrap: wrap; gap: .4rem; padding-top: .7rem; border-top: 1px solid rgb(15 23 42 / 5%); }
        .sc-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr)); gap: .85rem; align-items: end; }
        label { display: grid; gap: .28rem; font-size: .78rem; font-weight: 600; color: #4b5563; }
        input[type=text], select {
            width: 100%;
            border: 1px solid transparent;
            border-radius: .45rem;
            padding: .5rem .7rem;
            font: inherit;
            background: #f3f4f6;
            color: #111827;
            transition: background-color 120ms ease, box-shadow 120ms ease;
        }
        input[type=text]:hover, select:hover { background: #ebedf0; }
        input[type=text]:focus, select:focus {
            outline: none;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgb(0 102 255 / 22%);
        }
        .sc-alert { padding: .75rem 1rem; border-radius: .5rem; background: #fef2f2; color: #b91c1c; font-weight: 550; }
    </style>
</head>
<body>
<header class="sc-head">
    <div class="sc-head-inner">
        <div class="sc-title">
            @include('spinotek-certificates::partials.brand-logo')
            <span class="sc-brand">Spinotek</span>
            <h1>Certificate Creator</h1>
        </div>
        <div style="display:flex;gap:.45rem;flex-wrap:wrap;">
            <a href="{{ route('spinotek.certificates.verify.index') }}" target="_blank" class="sc-btn sc-btn-ghost">Halaman Verifikasi</a>
            <a href="{{ $backUrl }}" class="sc-btn sc-btn-sky">Kembali ke Aplikasi</a>
        </div>
    </div>
</header>

<main class="sc-container">
    @if (session('error'))
        <div class="sc-alert">{{ session('error') }}</div>
    @endif

    <section class="sc-card">
        <h2 style="margin:0 0 .9rem;font-size:1rem;font-weight:650;color:#111827;">Buat Template Baru</h2>
        <form method="POST" action="{{ route('spinotek.design.store') }}" class="sc-form-grid">
            @csrf
            <label>
                Nama Template
                <input type="text" name="name" required placeholder="Contoh: Sertifikat E-Course Premium">
            </label>
            <label>
                Kategori Produk / Program
                <select name="category">
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Format Nomor Sertifikat
                <input type="text" name="number_format" value="{{ $defaultNumberFormat }}" placeholder="CERT/{CAT}/{YYYY}/{SEQ:5}">
            </label>
            <label>
                Mode Awal Desain
                <select name="starter_mode">
                    <option value="blank">Kanvas Bersih (Hanya QR — untuk Gambar Dasar)</option>
                    <option value="default">Layout Lengkap Bawaan</option>
                </select>
            </label>
            @if (! empty($backgroundPresets))
                <label>
                    Gambar Dasar Preset (Opsional)
                    <select name="preset_background">
                        <option value="">-- Tanpa Gambar Dasar (Pilih di Editor) --</option>
                        @foreach ($backgroundPresets as $preset)
                            <option value="{{ $preset['key'] }}">{{ $preset['label'] }} ({{ $preset['width'] }}×{{ $preset['height'] }})</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <label style="display:flex;align-items:center;gap:.45rem;padding-bottom:.45rem;color:#111827;">
                <input type="checkbox" name="is_default" value="1" style="accent-color:#0066ff;"> Jadikan default kategori
            </label>
            <div>
                <button type="submit" class="sc-btn sc-btn-primary" style="width:100%;">+ Buat &amp; Desain</button>
            </div>
        </form>
    </section>

    <section class="sc-grid">
        @foreach ($designs as $design)
            <article class="sc-card sc-tpl">
                <div>
                    <div class="sc-badges" style="margin-bottom:.6rem;">
                        <span class="sc-badge">{{ $design->categoryLabel() }}</span>
                        @if ($design->is_default)
                            <span class="sc-badge sc-badge-default">Default</span>
                        @endif
                    </div>
                    <h3 style="margin:0 0 .45rem;font-size:1.02rem;font-weight:650;color:#111827;">{{ $design->name }}</h3>
                    <div class="sc-meta">
                        <div>Slug: <code>{{ $design->slug }}</code></div>
                        <div>Format Nomor: <code>{{ $design->number_format ?: $defaultNumberFormat }}</code></div>
                        <div>Ukuran: {{ $design->layout['page']['width'] ?? 297 }} × {{ $design->layout['page']['height'] ?? 210 }} mm · {{ count($design->layout['elements'] ?? []) }} elemen</div>
                    </div>
                </div>
                <div class="sc-tpl-actions">
                    <a href="{{ route('spinotek.design.edit', $design) }}" class="sc-btn sc-btn-primary" style="flex:1;">Buka Editor</a>
                    <form method="POST" action="{{ route('spinotek.design.duplicate', $design) }}">
                        @csrf
                        <button type="submit" class="sc-btn">Duplikat</button>
                    </form>
                    @unless ($design->is_default)
                        <form method="POST" action="{{ route('spinotek.design.default', $design) }}">
                            @csrf
                            <button type="submit" class="sc-btn">Set Default</button>
                        </form>
                    @endunless
                    @if ($designs->count() > 1)
                        <form method="POST" action="{{ route('spinotek.design.destroy', $design) }}" onsubmit="return confirm('Hapus template ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sc-btn sc-btn-danger">Hapus</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </section>
</main>
</body>
</html>
