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
    <script type="application/json" data-sample>@json($sample)</script>
    <fieldset class="sc-work">
        <header class="sc-head">
            <h1>Editor sertifikat</h1>
            <div class="sc-actions">
                <button type="button" data-action="preview">Preview PDF</button>
                <button type="button" data-action="save" class="sc-primary">Simpan desain</button>
            </div>
        </header>
        <p role="status" aria-live="polite" class="sc-status"></p>
        <div class="sc-workspace">
            <section class="sc-canvas-panel" aria-label="Kanvas desain">
                <div class="sc-sheet-info"><strong>Halaman sertifikat</strong><span>{{ $layout['page']['width'] }} × {{ $layout['page']['height'] }} mm</span></div>
                <div class="sc-sheet">
                    <img class="sc-bg" alt="" hidden>
                    <div class="sc-frame" aria-hidden="true"></div>
                    <div class="sc-nodes"></div>
                </div>
                <div class="sc-paper-options">
                    <label>Latar JPG / PNG <small>(maks. 1 MiB)</small><input type="file" accept="image/jpeg,image/png" data-upload-file></label>
                    <button type="button" data-action="clear-bg" hidden>Hapus latar</button>
                    <label class="sc-check"><input type="checkbox" data-frame> Tampilkan bingkai</label>
                </div>
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
                <div class="sc-pair">
                    <label>X (mm)<input type="number" min="0" step="0.1" data-key="x"></label>
                    <label>Y (mm)<input type="number" min="0" step="0.1" data-key="y"></label>
                    <label><span data-width-label>Lebar (mm)</span><input type="number" step="0.1" data-key="width"></label>
                    <label data-text-only>Tinggi (mm)<input type="number" step="0.1" data-key="height"></label>
                </div>
                <div class="sc-pair" data-text-only>
                    <label>Font<select data-key="font"><option value="sans">Sans serif</option><option value="serif">Serif</option><option value="mono">Monospace</option></select></label>
                    <label>Ukuran (pt)<input type="number" data-key="font_size" min="1" max="200" step="0.5"></label>
                    <label>Warna<input type="color" data-key="color"></label>
                    <label>Perataan<select data-key="align"><option value="left">Kiri</option><option value="center">Tengah</option><option value="right">Kanan</option></select></label>
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
