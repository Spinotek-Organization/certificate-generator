<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Editor Sertifikat — {{ $design->name }}</title>
    @if ($logoDataUri = \Spinotek\CertificateGenerator\CertificateServiceProvider::logoDataUri())
        <link rel="icon" type="image/png" href="{{ $logoDataUri }}">
    @endif
    <style>@include('spinotek-certificates::editor.style')</style>
</head>
<body>
<main class="sc-editor"
      data-upload="{{ route('spinotek.design.upload') }}"
      data-preview="{{ route('spinotek.design.preview') }}"
      data-save="{{ route('spinotek.design.save', $design) }}"
      data-token="{{ csrf_token() }}"
      data-qr="data:image/png;base64,{{ $qr }}">
    <script type="application/json" data-layout>@json($layout)</script>
    <script type="application/json" data-palettes>@json($palettes)</script>
    <script type="application/json" data-variables>@json($variables)</script>
    <script type="application/json" data-sample>@json($sample)</script>
    <script type="application/json" data-frame-lines>@json($frameLines)</script>
    <script type="application/json" data-presets>@json($backgroundPresets)</script>
    <script type="application/json" data-meta>@json($meta)</script>

    <fieldset class="sc-work">
        <header class="sc-head">
            <div class="sc-head-inner">
                <div class="sc-title">
                    @include('spinotek-certificates::partials.brand-logo')
                    <span class="sc-brand">Spinotek</span>
                    <h1>{{ $design->name }}</h1>
                </div>
                <nav class="sc-topnav" aria-label="Menu editor">
                    <a href="{{ $backUrl }}">Template</a>
                    <span class="sc-topnav-active" aria-current="page">Editor</span>
                    <a href="{{ route('spinotek.certificates.verify.index') }}" target="_blank">Verifikasi ↗</a>
                </nav>
                <div class="sc-actions">
                    <a href="{{ $backUrl }}" class="sc-head-link">Daftar Template</a>
                    <button type="button" data-action="preview">Pratinjau PDF</button>
                    <button type="button" data-action="save" class="sc-primary">Simpan desain</button>
                </div>
            </div>
        </header>

        <div class="sc-workspace">
            <aside class="sc-left-sidebar" aria-label="Navigasi template">
                <div class="sc-side-group">
                    <div class="sc-side-label">Navigasi</div>
                    <a href="{{ $backUrl }}" class="sc-side-link">← Semua Template</a>
                    <a href="{{ route('spinotek.certificates.verify.index') }}" target="_blank" class="sc-side-link">
                        <span>Halaman Verifikasi</span>
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>

                <div class="sc-side-group">
                    <div class="sc-side-label">Template Lainnya</div>
                    <div class="sc-side-scroll">
                        @foreach ($designs as $item)
                            <a href="{{ route('spinotek.design.edit', $item) }}"
                               @class(['sc-side-link', 'is-active' => $item->id === $design->id])
                               title="{{ $item->name }}">
                                <span class="sc-side-item-text">{{ $item->name }}</span>
                                <span class="sc-side-cat">{{ $item->categoryLabel() }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="sc-checklist-card" aria-label="Status desain template">
                    <div class="sc-checklist-top">
                        <span class="sc-checklist-title">Status Desain</span>
                        <span class="sc-checklist-badge" data-checklist-ratio>3/3</span>
                    </div>
                    <div class="sc-progress-track" aria-hidden="true">
                        <div class="sc-progress-fill" data-checklist-bar></div>
                    </div>
                    <div class="sc-checklist-items">
                        <div class="sc-cl-row" data-check-qr>
                            <span class="sc-cl-icon" aria-hidden="true">
                                <svg viewBox="0 0 16 16"><polyline points="3.5 8.5 6.5 11.5 12.5 4.5"/></svg>
                            </span>
                            <span>QR verifikasi aktif</span>
                        </div>
                        <div class="sc-cl-row" data-check-elements>
                            <span class="sc-cl-icon" aria-hidden="true">
                                <svg viewBox="0 0 16 16"><polyline points="3.5 8.5 6.5 11.5 12.5 4.5"/></svg>
                            </span>
                            <span>{{ count($layout['elements'] ?? []) }} elemen terpasang</span>
                        </div>
                        <div class="sc-cl-row" data-check-saved>
                            <span class="sc-cl-icon is-saved" aria-hidden="true">
                                <svg viewBox="0 0 16 16"><polyline points="3.5 8.5 6.5 11.5 12.5 4.5"/></svg>
                            </span>
                            <span>Tersimpan</span>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="sc-canvas-panel" aria-label="Kanvas desain">
                <div class="sc-sheet-info">
                    <div class="sc-sheet-title">
                        <h2>Kanvas sertifikat</h2>
                        <span data-page-size>{{ $layout['page']['width'] }} × {{ $layout['page']['height'] }} mm</span>
                    </div>
                    <div class="sc-sheet-meta">
                        <p role="status" aria-live="polite" class="sc-status" hidden></p>
                        <button type="button" data-open-appearance aria-controls="sc-theme" class="sc-palette-tag">
                            <span class="sc-palette-dot" aria-hidden="true"></span>
                            <span data-active-palette></span>
                            <span aria-hidden="true" class="sc-chevron"></span>
                        </button>
                    </div>
                </div>
                <div class="sc-sheet-stage">
                    <div class="sc-sheet">
                        <img class="sc-bg" alt="" hidden>
                        <div class="sc-frame" aria-hidden="true"></div>
                        <div class="sc-nodes"></div>
                    </div>
                </div>
                <p class="sc-canvas-tip">Geser elemen untuk memindahkan, tarik titik sudut/tepi untuk mengubah ukuran, atau gunakan tombol panah.</p>
            </section>

            <aside class="sc-inspector" aria-label="Pengaturan sertifikat">
                <div class="sc-tabs sc-tabs-3" role="tablist" aria-label="Pengaturan">
                    <button type="button" id="sc-elements-tab" role="tab" aria-selected="true" aria-controls="sc-elements">Elemen</button>
                    <button type="button" id="sc-design-tab" role="tab" aria-selected="false" aria-controls="sc-theme" tabindex="-1">Desain</button>
                    <button type="button" id="sc-settings-tab" role="tab" aria-selected="false" aria-controls="sc-settings" tabindex="-1">Pengaturan</button>
                </div>

                <section id="sc-elements" class="sc-properties" role="tabpanel" aria-labelledby="sc-elements-tab">
                    <label>Pilih elemen<select data-selection></select></label>
                    <div class="sc-add">
                        <label>Elemen baru<select data-add-type></select></label>
                        <button type="button" data-action="add">Tambah</button>
                    </div>
                    <h2 class="sc-group-heading">Properti elemen</h2>
                    <label data-fixed>Isi teks<textarea data-key="text" maxlength="2000" rows="2"></textarea></label>
                    <label data-variable>Sumber data<select data-key="field"></select></label>
                    <div class="sc-image-controls" data-image-only hidden>
                        <label>Nama elemen<input type="text" data-key="label" maxlength="80"></label>
                        <button type="button" data-action="replace-image">Ganti gambar</button>
                    </div>
                    <input type="file" accept="image/jpeg,image/png" data-upload-image hidden>
                    <div class="sc-pair">
                        <label>X (mm)<input type="number" min="0" step="0.1" data-key="x"></label>
                        <label>Y (mm)<input type="number" min="0" step="0.1" data-key="y"></label>
                        <label><span data-width-label>Lebar (mm)</span><input type="number" step="0.1" data-key="width"></label>
                        <label data-height>Tinggi (mm)<input type="number" step="0.1" data-key="height"></label>
                    </div>
                    <div class="sc-pair" data-text-only>
                        <label>Font<select data-key="font"><option value="sans">Sans serif</option><option value="serif">Serif</option><option value="mono">Monospace</option></select></label>
                        <label>Ukuran (pt)<input type="number" data-key="font_size" min="1" max="200" step="0.5"></label>
                        <label>Perataan<select data-key="align"><option value="left">Kiri</option><option value="center">Tengah</option><option value="right">Kanan</option></select></label>
                        <div class="sc-color-field">
                            <label for="sc-el-color">Warna teks</label>
                            <div class="sc-color-inputs">
                                <input id="sc-el-color" type="color" data-key="color">
                                <input type="text" data-key-hex="color" maxlength="7" spellcheck="false" autocomplete="off" aria-label="Kode HEX warna teks" placeholder="#14233c">
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;justify-content:space-between;">
                        <button type="button" data-action="delete" class="sc-delete">Hapus elemen</button>
                        <button type="button" data-action="clear-elements" class="sc-delete" title="Hapus semua teks/gambar dan sisakan QR Code">Bersihkan (Sisakan QR)</button>
                    </div>
                </section>

                <section id="sc-theme" class="sc-theme-panel" role="tabpanel" aria-labelledby="sc-design-tab" hidden>
                    <h2>Gambar dasar sertifikat</h2>
                    <div class="sc-paper-options">
                        @if (! empty($backgroundPresets))
                            <div class="sc-preset-grid" role="group" aria-label="Galeri gambar dasar preset">
                                @foreach ($backgroundPresets as $preset)
                                    <button type="button" class="sc-preset-card" data-preset-bg="{{ $preset['key'] }}" title="{{ $preset['label'] }}">
                                        <img src="{{ $preset['preview_url'] }}" alt="{{ $preset['label'] }}">
                                        <span>{{ $preset['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        <label class="sc-upload-field">Upload Latar JPG / PNG <small>(maks. 5 MB, kualitas asli)</small><input type="file" accept="image/jpeg,image/png" data-upload-file></label>
                        <button type="button" data-action="clear-bg" hidden>Hapus gambar dasar</button>
                        <label>Orientasi / Ukuran Kanvas
                            <select data-orientation>
                                <option value="landscape">A4 Landscape (297 × 210 mm)</option>
                                <option value="portrait">A4 Portrait (210 × 297 mm)</option>
                                <option value="auto" hidden>Sesuai Rasio Gambar</option>
                            </select>
                        </label>
                        <label class="sc-check"><input type="checkbox" data-frame> Tampilkan bingkai bawaan</label>
                    </div>

                    <h2 class="sc-group-heading">Palet warna</h2>
                    <div class="sc-palettes" role="group" aria-label="Pilihan palet bawaan">
                        @foreach ($palettes as $key => $palette)
                            <button type="button" class="sc-palette-btn" data-palette="{{ $key }}" aria-pressed="false">
                                <span class="sc-swatches" aria-hidden="true">
                                    <span style="background:{{ $palette['background'] }}"></span>
                                    <span style="background:{{ $palette['frame'] }}"></span>
                                    <span style="background:{{ $palette['accent'] }}"></span>
                                </span>
                                <span class="sc-palette-name">{{ $palette['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <div class="sc-theme-colors">
                        <div class="sc-color-field">
                            <label for="sc-theme-background">Warna latar</label>
                            <div class="sc-color-inputs">
                                <input id="sc-theme-background" type="color" data-theme-color="background" aria-label="Pilih warna latar">
                                <input type="text" data-theme-hex="background" maxlength="7" spellcheck="false" autocomplete="off" aria-label="Kode HEX warna latar" placeholder="#fffdf8">
                            </div>
                        </div>
                        <div class="sc-color-field">
                            <label for="sc-theme-accent">Warna aksen</label>
                            <div class="sc-color-inputs">
                                <input id="sc-theme-accent" type="color" data-theme-color="accent" aria-label="Pilih warna aksen">
                                <input type="text" data-theme-hex="accent" maxlength="7" spellcheck="false" autocomplete="off" aria-label="Kode HEX warna aksen" placeholder="#a27d34">
                            </div>
                        </div>
                        <div class="sc-color-field">
                            <label for="sc-theme-frame">Warna bingkai</label>
                            <div class="sc-color-inputs">
                                <input id="sc-theme-frame" type="color" data-theme-color="frame" aria-label="Pilih warna bingkai">
                                <input type="text" data-theme-hex="frame" maxlength="7" spellcheck="false" autocomplete="off" aria-label="Kode HEX warna bingkai" placeholder="#14233c">
                            </div>
                        </div>
                    </div>
                    <p class="sc-contrast-note" data-contrast-note hidden></p>
                </section>

                <section id="sc-settings" class="sc-properties" role="tabpanel" aria-labelledby="sc-settings-tab" hidden>
                    <h2>Info Template</h2>
                    <label>Nama template<input type="text" maxlength="120" data-meta-input="name"></label>
                    <label>Kategori
                        <select data-meta-input="category">
                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="sc-check"><input type="checkbox" data-meta-input="is_default"> Template default untuk kategori ini</label>

                    <h2 class="sc-group-heading">Nomor Sertifikat Custom</h2>
                    <label>Format nomor
                        <input type="text" maxlength="120" data-meta-input="number_format" placeholder="CERT/{CAT}/{YYYY}/{SEQ:5}">
                    </label>
                    <p class="sc-token-help">
                        Token: <code>{CAT}</code>, <code>{CATEGORY}</code>, <code>{YYYY}</code>, <code>{YY}</code>, <code>{MM}</code>, <code>{DD}</code>, <code>{SEQ:5}</code>, <code>{RAND:4}</code>, <code>{UUID_SHORT}</code>
                    </p>

                    <h2 class="sc-group-heading">Variabel Custom</h2>
                    <div class="sc-custom-var-form">
                        <label>Key variabel <small>(huruf kecil &amp; garis bawah)</small><input type="text" data-cv-key placeholder="mis. predicate"></label>
                        <label>Label tampilan<input type="text" data-cv-label placeholder="mis. Predikat Kelulusan"></label>
                        <label>Contoh teks pratinjau<input type="text" data-cv-sample placeholder="mis. Sangat Baik"></label>
                        <button type="button" data-action="add-custom-var">+ Tambah Variabel</button>
                    </div>
                    <div class="sc-custom-var-list" data-custom-var-list></div>
                </section>
            </aside>
        </div>
    </fieldset>

    <dialog class="sc-preview" aria-labelledby="sc-preview-title">
        <div><h2 id="sc-preview-title">Pratinjau PDF</h2><a data-preview-link target="_blank" rel="noopener">Buka PDF</a><button type="button" data-close>Tutup</button></div>
        <iframe title="Pratinjau PDF sertifikat"></iframe>
    </dialog>
    <script>@include('spinotek-certificates::editor.script')</script>
    <script>
    (() => {
        const nodes = document.querySelector('.sc-nodes');
        const statusEl = document.querySelector('.sc-status');
        const countText = document.querySelector('[data-check-elements] span:last-child');
        const savedRow = document.querySelector('[data-check-saved]');
        const ratioEl = document.querySelector('[data-checklist-ratio]');
        const barEl = document.querySelector('[data-checklist-bar]');
        if (!nodes || !countText || !savedRow) return;

        const updateCount = () => {
            countText.textContent = nodes.querySelectorAll('.sc-node').length + ' elemen terpasang';
        };
        new MutationObserver(updateCount).observe(nodes, {childList: true});
        if (statusEl) {
            new MutationObserver(() => {
                const text = statusEl.textContent || '';
                const icon = savedRow.querySelector('.sc-cl-icon');
                const label = savedRow.querySelector('span:last-child');
                if (text.includes('Perubahan belum disimpan')) {
                    icon?.classList.remove('is-saved');
                    icon?.classList.add('is-dirty');
                    if (icon) icon.innerHTML = '<svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="2.5" fill="currentColor" stroke="none"/></svg>';
                    if (label) label.textContent = 'Belum disimpan';
                    if (ratioEl) { ratioEl.textContent = '2/3'; ratioEl.classList.add('is-dirty'); }
                    barEl?.classList.add('is-dirty');
                } else if (text.includes('tersimpan')) {
                    icon?.classList.remove('is-dirty');
                    icon?.classList.add('is-saved');
                    if (icon) icon.innerHTML = '<svg viewBox="0 0 16 16"><polyline points="3.5 8.5 6.5 11.5 12.5 4.5"/></svg>';
                    if (label) label.textContent = 'Tersimpan';
                    if (ratioEl) { ratioEl.textContent = '3/3'; ratioEl.classList.remove('is-dirty'); }
                    barEl?.classList.remove('is-dirty');
                }
            }).observe(statusEl, {childList: true, characterData: true, subtree: true, attributes: true});
        }
    })();
    </script>
</main>
</body>
</html>
