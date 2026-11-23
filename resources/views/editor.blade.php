<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Editor sertifikat</title>
    <style>@include('spinotek-certificates::editor.style')</style>
</head>
<body>
<main class="sc-editor"
      data-upload="{{ route('spinotek.design.upload') }}"
      data-preview="{{ route('spinotek.design.preview') }}"
      data-save="{{ route('spinotek.design.save') }}"
      data-token="{{ csrf_token() }}" data-qr="data:image/png;base64,{{ $qr }}">
    <script type="application/json" data-layout>@json($layout)</script>
    <script type="application/json" data-palettes>@json($palettes)</script>
    <script type="application/json" data-sample>@json($sample)</script>
    <fieldset class="sc-work">
        <header class="sc-head">
            <div class="sc-title">
                <h1>Editor sertifikat</h1>
                <p>Pilih palet, tambahkan logo, dan atur posisi elemen sertifikat.</p>
            </div>
            <div class="sc-actions">
                <button type="button" data-action="preview">Preview PDF</button>
                <button type="button" data-action="save" class="sc-primary">Simpan desain</button>
            </div>
        </header>
        <p role="status" aria-live="polite" class="sc-status"></p>
        <div class="sc-workspace">
            <section class="sc-canvas-panel" aria-label="Kanvas desain">
                <div class="sc-sheet-info">
                    <strong>Halaman sertifikat</strong>
                    <div class="sc-sheet-meta">
                        <span class="sc-palette-tag" data-active-palette></span>
                        <span>{{ $layout['page']['width'] }} × {{ $layout['page']['height'] }} mm</span>
                        <a href="#sc-theme">Atur warna</a>
                    </div>
                </div>
                <div class="sc-sheet-stage">
                    <div class="sc-sheet">
                        <img class="sc-bg" alt="" hidden>
                        <div class="sc-frame" aria-hidden="true"></div>
                        <div class="sc-nodes"></div>
                    </div>
                </div>
                <section id="sc-theme" class="sc-theme-panel" aria-labelledby="sc-theme-heading">
                    <div class="sc-panel-head">
                        <h2 id="sc-theme-heading">Palet &amp; warna halaman</h2>
                        <span class="sc-hint">Berlaku di pratinjau dan PDF terbit</span>
                    </div>
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
                                <input type="text" data-theme-hex="background" maxlength="7" spellcheck="false" autocomplete="off" aria-label="Kode HEX warna latar" placeholder="#faf6ee">
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
                    <hr>
                    <div class="sc-paper-options">
                        <label class="sc-upload-field">Latar JPG / PNG <small>(maks. 1 MiB)</small><input type="file" accept="image/jpeg,image/png" data-upload-file></label>
                        <button type="button" data-action="clear-bg" hidden>Hapus latar</button>
                        <label class="sc-check"><input type="checkbox" data-frame> Tampilkan bingkai</label>
                    </div>
                </section>
            </section>
            <section class="sc-properties" aria-label="Properti elemen">
                <h2>Elemen</h2>
                <label>Pilih elemen<select data-selection></select></label>
                <div class="sc-add">
                    <label>Elemen baru<select data-add-type></select></label>
                    <button type="button" data-action="add">Tambah</button>
                </div>
                <hr>
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
                <button type="button" data-action="delete" class="sc-delete">Hapus elemen</button>
            </section>
        </div>
    </fieldset>
    <dialog class="sc-preview" aria-labelledby="sc-preview-title">
        <div><h2 id="sc-preview-title">Preview PDF</h2><a data-preview-link target="_blank" rel="noopener">Buka PDF</a><button type="button" data-close>Tutup</button></div>
        <iframe title="Preview PDF sertifikat"></iframe>
    </dialog>
    <script>@include('spinotek-certificates::editor.script')</script>
</main>
</body>
</html>
