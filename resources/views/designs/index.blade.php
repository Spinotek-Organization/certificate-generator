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
        body { margin: 0; background: #f2f6fc; color: #0f1f38; font: .92rem/1.5 system-ui, -apple-system, sans-serif; }
        .sc-head { background: linear-gradient(135deg, #07142b 0%, #0d244d 58%, #0a3b7a 100%); color: #f4f8ff; border-bottom: 1px solid #091a38; box-shadow: 0 4px 18px rgb(7 20 43 / 18%); }
        .sc-head-inner { max-width: 78rem; margin: auto; padding: .55rem 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: .85rem; flex-wrap: wrap; }
        .sc-title { display: flex; align-items: center; gap: .6rem; }
        .sc-logo-badge { width: 1.85rem; height: 1.85rem; border-radius: .48rem; background: #ffffff; display: inline-flex; align-items: center; justify-content: center; padding: .18rem; box-shadow: 0 2px 8px rgb(0 150 255 / 26%); flex-shrink: 0; }
        .sc-logo-img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .sc-brand { color: #7cd4ff; font-weight: 700; letter-spacing: .01em; padding-right: .75rem; border-right: 1px solid rgb(124 212 255 / 28%); font-size: .9rem; }
        h1 { margin: 0; font-size: 1.12rem; font-weight: 700; letter-spacing: -.01em; }
        .sc-container { max-width: 78rem; margin: 1.65rem auto 3rem; padding: 0 1.5rem; display: grid; gap: 1.5rem; }
        .sc-btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; border: 1px solid #cbd9ee; border-radius: .5rem; background: #ffffff; color: #0f1f38; padding: .52rem .95rem; font: inherit; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 140ms ease; }
        .sc-btn:hover { background: #edf3fc; border-color: #9cb8e3; }
        .sc-btn-primary { background: linear-gradient(135deg, #0088ff, #0052e0); border-color: #005ce6; color: #ffffff; box-shadow: 0 2px 8px rgb(0 92 230 / 24%); }
        .sc-btn-primary:hover { background: linear-gradient(135deg, #0077eb, #0046c7); border-color: #0046c7; color: #ffffff; }
        .sc-btn-ghost { background: rgb(255 255 255 / 10%); border-color: rgb(255 255 255 / 24%); color: #f4f8ff; }
        .sc-btn-ghost:hover { background: rgb(255 255 255 / 18%); border-color: rgb(255 255 255 / 38%); color: #ffffff; }
        .sc-btn-sky { background: #e8f3ff; border-color: #b7d7ff; color: #0048b8; }
        .sc-btn-sky:hover { background: #d6e9ff; border-color: #8cc0ff; }
        .sc-btn-danger { color: #b42318; border-color: #f3c4be; }
        .sc-btn-danger:hover { background: #fff4f2; border-color: #d92d20; }
        .sc-card { background: #ffffff; border: 1px solid #dbe5f3; border-radius: .9rem; padding: 1.3rem; box-shadow: 0 .35rem 1.25rem rgb(15 31 56 / 5%); }
        .sc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(19.5rem, 1fr)); gap: 1rem; }
        .sc-tpl { display: grid; gap: .85rem; align-content: space-between; transition: border-color 140ms ease, box-shadow 140ms ease; }
        .sc-tpl:hover { border-color: #9ec4f8; box-shadow: 0 .5rem 1.4rem rgb(0 82 224 / 8%); }
        .sc-badges { display: flex; flex-wrap: wrap; gap: .4rem; }
        .sc-badge { font-size: .75rem; font-weight: 650; padding: .16rem .6rem; border-radius: 999px; background: #eaf2ff; color: #004ec2; }
        .sc-badge-default { background: #dff5ec; color: #0c6b43; }
        .sc-meta { color: #526582; font-size: .82rem; display: grid; gap: .28rem; }
        .sc-meta code { background: #eef4fc; color: #0b3a78; padding: .12rem .38rem; border-radius: .28rem; font-size: .78rem; }
        .sc-tpl-actions { display: flex; flex-wrap: wrap; gap: .45rem; padding-top: .65rem; border-top: 1px solid #e9f0fa; }
        .sc-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr)); gap: .85rem; align-items: end; }
        label { display: grid; gap: .32rem; font-size: .82rem; font-weight: 600; color: #1d3557; }
        input[type=text], select { width: 100%; border: 1px solid #c6d6ec; border-radius: .5rem; padding: .52rem .7rem; font: inherit; background: #fbfdff; color: #0f1f38; }
        input[type=text]:focus, select:focus { outline: 2px solid #0066ff; outline-offset: 1px; border-color: #0066ff; background: #ffffff; }
        .sc-alert { padding: .75rem 1rem; border-radius: .55rem; background: #fff4f2; border: 1px solid #f3c4be; color: #b42318; font-weight: 600; }
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
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
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
        <h2 style="margin:0 0 .9rem;font-size:1.05rem;color:#0b254b;">Buat Template Baru</h2>
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
            <label style="display:flex;align-items:center;gap:.45rem;padding-bottom:.45rem;">
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
                    <h3 style="margin:0 0 .45rem;font-size:1.05rem;color:#0f1f38;">{{ $design->name }}</h3>
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
