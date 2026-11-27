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
            background: #f6f7fb;
            color: #111827;
            font: .89rem/1.5 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; }

        /* Top Navigation Bar (3-zone like SaaS shell reference) */
        .sc-topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            height: 3.1rem;
            background: #ffffff;
            border-bottom: 1px solid #eaedf2;
            padding: 0 1.25rem;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 1rem;
        }
        .sc-brand-wrap { display: flex; align-items: center; gap: .55rem; min-width: 0; }
        .sc-logo-badge { width: 1.65rem; height: 1.65rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .sc-logo-img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .sc-brand { font-weight: 700; font-size: .95rem; color: #0f172a; letter-spacing: -.015em; }

        .sc-topnav { display: flex; align-items: center; gap: .35rem; }
        .sc-topnav a {
            padding: .38rem .85rem;
            border-radius: .5rem;
            font-size: .84rem;
            font-weight: 550;
            color: #6b7280;
            text-decoration: none;
            transition: background-color 120ms ease, color 120ms ease;
        }
        .sc-topnav a:hover { color: #111827; background: #f3f5f8; }
        .sc-topnav a[aria-current="page"] { background: #f3f5f8; color: #111827; font-weight: 600; }

        .sc-top-actions { display: flex; align-items: center; justify-content: flex-end; gap: .5rem; }

        /* App Shell: Left Sidebar + Main Content */
        .sc-shell {
            display: grid;
            grid-template-columns: 14.75rem minmax(0, 1fr);
            min-height: calc(100dvh - 3.1rem);
        }
        .sc-sidebar {
            background: #ffffff;
            border-right: 1px solid #eaedf2;
            padding: 1.1rem .85rem;
            display: flex;
            flex-direction: column;
            gap: 1.35rem;
        }
        .sc-side-group { display: grid; gap: .22rem; }
        .sc-side-label {
            padding: 0 .65rem .3rem;
            font-size: .74rem;
            font-weight: 650;
            color: #6b7280;
            letter-spacing: .01em;
        }
        .sc-side-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            width: 100%;
            border: 0;
            background: transparent;
            padding: .48rem .7rem;
            border-radius: .5rem;
            font: inherit;
            font-size: .84rem;
            font-weight: 500;
            color: #4b5563;
            text-decoration: none;
            text-align: left;
            cursor: pointer;
            transition: background-color 120ms ease, color 120ms ease;
        }
        .sc-side-link:hover { background: #f3f5f8; color: #111827; }
        .sc-side-link.is-active { background: #0f172a; color: #ffffff; font-weight: 600; }
        .sc-side-count {
            font-size: .73rem;
            font-weight: 600;
            color: #6b7280;
            background: #f3f5f8;
            padding: .06rem .45rem;
            border-radius: 999px;
        }
        .sc-side-link.is-active .sc-side-count { background: rgb(255 255 255 / 18%); color: #ffffff; }
        .sc-side-item-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Main Area */
        .sc-main {
            padding: 1.5rem 1.75rem 3rem;
            display: grid;
            align-content: start;
            gap: 1.25rem;
            max-width: 72rem;
            width: 100%;
            margin: 0 auto;
        }
        .sc-page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .sc-page-head h1 { margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a; letter-spacing: -.02em; }
        .sc-page-sub { margin: .15rem 0 0; font-size: .83rem; color: #6b7280; }
        .sc-search {
            width: min(100%, 17.5rem);
            padding: .48rem .8rem;
            border: 1px solid #e2e6ee;
            border-radius: .55rem;
            background: #ffffff;
            color: #111827;
            font: inherit;
            font-size: .84rem;
        }
        .sc-search:focus { outline: none; border-color: #0066ff; box-shadow: 0 0 0 3px rgb(0 102 255 / 12%); }

        /* Crisp White Cards */
        .sc-card {
            background: #ffffff;
            border: 1px solid #eaedf2;
            border-radius: .75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 2px rgb(16 24 40 / 3%);
        }
        .sc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(19rem, 1fr)); gap: 1rem; }
        .sc-tpl { display: grid; gap: .9rem; align-content: space-between; transition: border-color 140ms ease, box-shadow 140ms ease; }
        .sc-tpl:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgb(15 23 42 / 5%); }
        .sc-tpl[hidden] { display: none; }

        .sc-badges { display: flex; flex-wrap: wrap; gap: .38rem; }
        .sc-badge { font-size: .73rem; font-weight: 600; padding: .14rem .55rem; border-radius: 999px; background: #eff6ff; color: #0052cc; }
        .sc-badge-default { background: #ecfdf5; color: #047857; }
        .sc-meta { color: #6b7280; font-size: .81rem; display: grid; gap: .25rem; }
        .sc-meta code { background: #f3f5f8; color: #1f2937; padding: .1rem .36rem; border-radius: .28rem; font-size: .77rem; }
        .sc-tpl-actions { display: flex; flex-wrap: wrap; gap: .4rem; padding-top: .75rem; border-top: 1px solid #f0f2f6; }

        /* Buttons & Form Controls */
        .sc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .38rem;
            border: 1px solid #e2e6ee;
            border-radius: .5rem;
            background: #ffffff;
            color: #374151;
            padding: .44rem .85rem;
            font: inherit;
            font-size: .83rem;
            font-weight: 550;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 120ms ease, border-color 120ms ease, color 120ms ease;
        }
        .sc-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #111827; }
        .sc-btn-primary { background: #0066ff; border-color: #0066ff; color: #ffffff; font-weight: 600; }
        .sc-btn-primary:hover { background: #0052e0; border-color: #0052e0; color: #ffffff; }
        .sc-btn-danger { color: #dc2626; border-color: #f3d0ce; }
        .sc-btn-danger:hover { background: #fef2f2; border-color: #dc2626; color: #b91c1c; }

        .sc-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(12.5rem, 1fr)); gap: .85rem; align-items: end; }
        label { display: grid; gap: .28rem; font-size: .78rem; font-weight: 600; color: #374151; }
        input[type=text], select {
            width: 100%;
            border: 1px solid #e2e6ee;
            border-radius: .48rem;
            padding: .48rem .7rem;
            font: inherit;
            background: #ffffff;
            color: #111827;
        }
        input[type=text]:focus, select:focus {
            outline: none;
            border-color: #0066ff;
            box-shadow: 0 0 0 3px rgb(0 102 255 / 12%);
        }
        .sc-alert { padding: .75rem 1rem; border-radius: .6rem; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-weight: 550; }
        .sc-empty { text-align: center; padding: 2rem; color: #6b7280; }

        @media (max-width: 58rem) {
            .sc-topbar { grid-template-columns: auto auto; }
            .sc-topnav { display: none; }
            .sc-shell { grid-template-columns: 1fr; }
            .sc-sidebar { border-right: 0; border-bottom: 1px solid #eaedf2; padding: .75rem 1.25rem; }
            .sc-main { padding: 1.15rem 1rem 2.5rem; }
        }
    </style>
</head>
<body>
<header class="sc-topbar">
    <div class="sc-brand-wrap">
        @include('spinotek-certificates::partials.brand-logo')
        <span class="sc-brand">Spinotek</span>
    </div>
    <nav class="sc-topnav" aria-label="Menu utama">
        <a href="{{ route('spinotek.design.index') }}" aria-current="page">Template</a>
        @if ($firstDesign = $designs->first())
            <a href="{{ route('spinotek.design.edit', $firstDesign) }}">Editor</a>
        @endif
        <a href="{{ route('spinotek.certificates.verify.index') }}" target="_blank">Verifikasi ↗</a>
    </nav>
    <div class="sc-top-actions">
        <a href="{{ $backUrl }}" class="sc-btn">← Kembali ke Aplikasi</a>
    </div>
</header>

<div class="sc-shell">
    <aside class="sc-sidebar" aria-label="Navigasi template">
        <div class="sc-side-group">
            <div class="sc-side-label">Menu</div>
            <button type="button" class="sc-side-link is-active" data-filter-cat="all">
                <span>Semua Template</span>
                <span class="sc-side-count">{{ $designs->count() }}</span>
            </button>
            <a href="#buat-template" class="sc-side-link" onclick="setTimeout(() => document.querySelector('input[name=name]')?.focus(), 60)">
                <span>+ Buat Template Baru</span>
            </a>
            <a href="{{ route('spinotek.certificates.verify.index') }}" target="_blank" class="sc-side-link">
                <span>Halaman Verifikasi</span>
                <span aria-hidden="true">↗</span>
            </a>
        </div>

        <div class="sc-side-group">
            <div class="sc-side-label">Kategori Program</div>
            @foreach ($categories as $key => $label)
                @php
                    $catCount = $designs->filter(fn ($d) => ($d->category ?? 'general') === $key)->count();
                @endphp
                <button type="button" class="sc-side-link" data-filter-cat="{{ $key }}">
                    <span>{{ $label }}</span>
                    <span class="sc-side-count">{{ $catCount }}</span>
                </button>
            @endforeach
        </div>

        <div class="sc-side-group">
            <div class="sc-side-label">Akses Cepat Editor</div>
            @foreach ($designs->take(8) as $quickDesign)
                <a href="{{ route('spinotek.design.edit', $quickDesign) }}" class="sc-side-link" title="{{ $quickDesign->name }}">
                    <span class="sc-side-item-text">{{ $quickDesign->name }}</span>
                </a>
            @endforeach
        </div>
    </aside>

    <main class="sc-main">
        <div class="sc-page-head">
            <div>
                <h1>Certificate Creator</h1>
                <p class="sc-page-sub">Kelola template sertifikat per kategori, format nomor, dan desain kanvas.</p>
            </div>
            <input type="search" class="sc-search" data-template-search placeholder="Cari nama template atau slug..." aria-label="Cari template">
        </div>

        @if (session('error'))
            <div class="sc-alert">{{ session('error') }}</div>
        @endif

        <section id="buat-template" class="sc-card">
            <h2 style="margin:0 0 .9rem;font-size:.98rem;font-weight:650;color:#0f172a;">Buat Template Baru</h2>
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

        <section class="sc-grid" data-template-grid>
            @foreach ($designs as $design)
                <article class="sc-card sc-tpl"
                         data-tpl-card
                         data-category="{{ $design->category ?? 'general' }}"
                         data-search="{{ strtolower($design->name.' '.$design->slug.' '.$design->categoryLabel()) }}">
                    <div>
                        <div class="sc-badges" style="margin-bottom:.6rem;">
                            <span class="sc-badge">{{ $design->categoryLabel() }}</span>
                            @if ($design->is_default)
                                <span class="sc-badge sc-badge-default">Default</span>
                            @endif
                        </div>
                        <h3 style="margin:0 0 .45rem;font-size:1.02rem;font-weight:650;color:#0f172a;">{{ $design->name }}</h3>
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
        <div class="sc-card sc-empty" data-empty-filter hidden>
            Tidak ada template yang cocok dengan filter atau pencarian Anda.
        </div>
    </main>
</div>

<script>
(() => {
    const filterButtons = [...document.querySelectorAll('[data-filter-cat]')];
    const searchInput = document.querySelector('[data-template-search]');
    const cards = [...document.querySelectorAll('[data-tpl-card]')];
    const emptyState = document.querySelector('[data-empty-filter]');
    let activeCategory = 'all';

    function applyFilter() {
        const term = (searchInput?.value || '').trim().toLowerCase();
        let visible = 0;
        for (const card of cards) {
            const matchCat = activeCategory === 'all' || card.dataset.category === activeCategory;
            const matchText = term === '' || (card.dataset.search || '').includes(term);
            const show = matchCat && matchText;
            card.hidden = !show;
            if (show) visible++;
        }
        if (emptyState) emptyState.hidden = visible > 0;
    }

    for (const btn of filterButtons) {
        btn.addEventListener('click', () => {
            activeCategory = btn.dataset.filterCat || 'all';
            for (const other of filterButtons) {
                other.classList.toggle('is-active', other === btn);
            }
            applyFilter();
        });
    }

    searchInput?.addEventListener('input', applyFilter);
})();
</script>
</body>
</html>
