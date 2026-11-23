(() => {
    const root = document.currentScript.closest('.sc-editor');
    const find = selector => root.querySelector(selector);
    const layout = JSON.parse(find('[data-layout]').textContent);
    const palettes = JSON.parse(find('[data-palettes]').textContent);
    const sample = JSON.parse(find('[data-sample]').textContent);
    const frameLines = JSON.parse(find('[data-frame-lines]').textContent);
    const sheet = find('.sc-sheet');
    const frameLayer = find('.sc-frame');
    const nodes = find('.sc-nodes');
    const selection = find('[data-selection]');
    const addType = find('[data-add-type]');
    const imageInput = find('[data-upload-image]');
    const controls = [...root.querySelectorAll('[data-key]')];
    const elementHexInput = find('[data-key-hex=color]');
    const paletteButtons = [...root.querySelectorAll('[data-palette]')];
    const activePaletteTag = find('[data-active-palette]');
    const paletteDot = find('.sc-palette-dot');
    const tabs = [...root.querySelectorAll('.sc-tabs [role=tab]')];
    const contrastNote = find('[data-contrast-note]');
    const status = find('.sc-status');
    const dialog = find('dialog');
    const work = find('.sc-work');
    const {width: pageW, height: pageH} = layout.page;
    const fields = {participant_name: 'Nama peserta', activity_title: 'Nama kegiatan', issuer: 'Penerbit', issued_date: 'Tanggal terbit', status: 'Status'};
    const legacyTheme = {legacy: true, palette: 'legacy', background: '#ffffff', accent: '#335148', frame: '#335148', text: '#243d35'};
    const fonts = {sans: '"DejaVu Sans", system-ui, sans-serif', serif: '"DejaVu Serif", Georgia, serif', mono: '"DejaVu Sans Mono", monospace'};
    const hexPattern = /^#[a-f0-9]{6}$/i;
    const buttons = new Map();
    let selected = layout.elements[0];
    let drag = null;
    let previewUrl = null;
    let dirty = false;
    let imageAction = null;

    const paletteTextRoles = {
        text: new Set([legacyTheme.text, ...Object.values(palettes).map(p => p.text.toLowerCase())]),
        muted: new Set(Object.values(palettes).map(p => p.muted.toLowerCase())),
        accent_text: new Set(Object.values(palettes).map(p => p.accent_text.toLowerCase())),
    };

    const imageLabels = {logo: 'Logo', signature: 'Tanda tangan'};
    addType.add(new Option('Teks tetap', 'text'));
    for (const [field, label] of Object.entries(fields)) {
        addType.add(new Option(label, field));
        find('[data-key=field]').add(new Option(label, field));
    }
    for (const [kind, label] of Object.entries(imageLabels)) {
        addType.add(new Option(label, kind));
    }
    sheet.style.aspectRatio = pageW + ' / ' + pageH;
    sheet.style.setProperty('--paper-ratio', pageW / pageH);

    function isHex(value) {
        return typeof value === 'string' && hexPattern.test(value);
    }

    function normalizeHex(value, fallback) {
        return isHex(value) ? value.toLowerCase() : fallback;
    }

    function luminance(hex) {
        const channels = [0, 2, 4].map(offset => {
            const c = parseInt(hex.slice(1 + offset, 3 + offset), 16) / 255;
            return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
        });
        return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
    }

    function contrastRatio(hexA, hexB) {
        const l1 = luminance(hexA);
        const l2 = luminance(hexB);
        return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
    }

    function resolveTheme() {
        if (!layout.theme || typeof layout.theme !== 'object') {
            return legacyTheme;
        }
        const base = palettes[layout.theme.palette] ?? palettes['midnight-navy'];
        const background = normalizeHex(layout.theme.background, base.background);
        const accent = normalizeHex(layout.theme.accent, base.accent);
        const frame = normalizeHex(layout.theme.frame, base.frame);
        let matched = null;
        for (const [key, preset] of Object.entries(palettes)) {
            if (background === preset.background && accent === preset.accent && frame === preset.frame) {
                matched = key;
                break;
            }
        }
        const text = matched ? palettes[matched].text : (luminance(background) > 0.35 ? '#14233c' : '#faf6ee');
        return {legacy: false, palette: matched ?? 'custom', background, accent, frame, text};
    }

    function ensureThemeObject() {
        const resolved = resolveTheme();
        if (!layout.theme || typeof layout.theme !== 'object') {
            layout.theme = {
                palette: 'custom',
                background: resolved.background,
                accent: resolved.accent,
                frame: resolved.frame,
            };
        }
        return layout.theme;
    }

    function message(text, error = false) {
        status.hidden = false;
        status.textContent = text;
        status.toggleAttribute('data-error', error);
    }

    function activateTab(active) {
        for (const tab of tabs) {
            const selectedTab = tab === active;
            tab.setAttribute('aria-selected', String(selectedTab));
            tab.tabIndex = selectedTab ? 0 : -1;
            find('#' + tab.getAttribute('aria-controls')).hidden = !selectedTab;
        }
    }

    for (const [index, tab] of tabs.entries()) {
        tab.addEventListener('click', () => activateTab(tab));
        tab.addEventListener('keydown', event => {
            const next = {
                ArrowLeft: (index - 1 + tabs.length) % tabs.length,
                ArrowRight: (index + 1) % tabs.length,
                Home: 0,
                End: tabs.length - 1,
            }[event.key];
            if (next === undefined) return;
            event.preventDefault();
            activateTab(tabs[next]);
            tabs[next].focus();
        });
    }
    find('[data-open-appearance]').addEventListener('click', () => {
        const appearance = find('#sc-design-tab');
        activateTab(appearance);
        appearance.focus();
    });

    function changed() {
        dirty = true;
        message('Perubahan belum disimpan.');
    }

    function captions(el) {
        if (el.type === 'qr') return 'QR verifikasi';
        if (el.type === 'image') return el.label || 'Gambar';
        return el.type === 'variable' ? fields[el.field] : el.text || 'Teks kosong';
    }

    function renderFrame(theme, visible) {
        frameLayer.replaceChildren();
        frameLayer.hidden = !visible;
        if (!visible) return;

        const addPart = style => {
            const part = document.createElement('div');
            part.className = 'sc-frame-part';
            Object.assign(part.style, style);
            frameLayer.append(part);
        };

        if (theme.legacy) {
            addPart({
                left: (8 / pageW * 100) + '%',
                top: (8 / pageH * 100) + '%',
                width: ((pageW - 16) / pageW * 100) + '%',
                height: ((pageH - 16) / pageH * 100) + '%',
                border: '1px solid ' + theme.frame,
            });
            return;
        }

        for (const [x, y, width, height, tone] of frameLines) {
            addPart({
                left: (x / pageW * 100) + '%',
                top: (y / pageH * 100) + '%',
                width: (width / pageW * 100) + '%',
                height: (height / pageH * 100) + '%',
                background: theme[tone],
            });
        }
    }

    function updateContrastNotice(theme) {
        if (layout.background) {
            contrastNote.hidden = true;
            return;
        }
        const low = layout.elements.filter(el => {
            if (el.type === 'qr' || el.type === 'image') return false;
            const color = isHex(el.color) ? el.color.toLowerCase() : theme.text;
            const ratio = contrastRatio(color, theme.background);
            const minRatio = Number(el.font_size) >= 18 ? 3.0 : 4.5;
            return ratio < minRatio;
        });
        if (low.length > 0) {
            contrastNote.textContent = 'Periksa kontras: ' + low.length + ' elemen teks memiliki kontras rendah terhadap warna latar.';
            contrastNote.hidden = false;
        } else {
            contrastNote.hidden = true;
        }
    }

    function syncThemeControls(theme) {
        paletteDot.style.background = theme.accent;
        for (const key of ['background', 'accent', 'frame']) {
            const colorInput = find('[data-theme-color=' + key + ']');
            const hexInput = find('[data-theme-hex=' + key + ']');
            colorInput.value = theme[key];
            if (document.activeElement !== hexInput || isHex(hexInput.value)) {
                hexInput.value = theme[key];
                hexInput.removeAttribute('aria-invalid');
            }
        }
        for (const btn of paletteButtons) {
            btn.setAttribute('aria-pressed', String(!theme.legacy && btn.dataset.palette === theme.palette));
        }
        if (theme.legacy) {
            activePaletteTag.textContent = 'Bawaan lama';
        } else if (palettes[theme.palette]) {
            activePaletteTag.textContent = palettes[theme.palette].label;
        } else {
            activePaletteTag.textContent = 'Palet kustom';
        }
    }

    function paper() {
        const theme = resolveTheme();
        const showFrame = layout.frame ?? true;
        const image = find('.sc-bg');
        sheet.style.background = theme.background;
        if (layout.background) image.src = layout.background;
        else image.removeAttribute('src');
        image.hidden = !layout.background;
        find('[data-action=clear-bg]').hidden = !layout.background;
        find('[data-frame]').checked = showFrame;
        renderFrame(theme, showFrame);
        syncThemeControls(theme);
        updateContrastNotice(theme);
        for (const el of layout.elements) {
            if (buttons.has(el.id)) paint(el);
        }
    }

    function paint(el) {
        const theme = resolveTheme();
        const showFrame = layout.frame ?? true;
        const button = buttons.get(el.id);
        Object.assign(button.style, {
            left: (el.x / pageW * 100) + '%', top: (el.y / pageH * 100) + '%',
            width: (el.width / pageW * 100) + '%', height: (el.height / pageH * 100) + '%'
        });
        button.setAttribute('aria-label', captions(el));
        button.setAttribute('aria-pressed', String(el === selected));
        if (el.type === 'qr') {
            const qrImg = button.querySelector('img');
            if (qrImg) {
                qrImg.style.border = (!theme.legacy && showFrame) ? ((0.25 / pageW * 100) + 'cqw solid ' + theme.accent) : 'none';
            }
            return;
        }
        if (el.type === 'image') {
            const image = button.querySelector('img');
            if (image.getAttribute('src') !== el.image) image.src = el.image;
            return;
        }
        button.textContent = el.type === 'text' ? el.text : sample[el.field];
        Object.assign(button.style, {
            fontFamily: fonts[el.font] ?? fonts.sans,
            color: isHex(el.color) ? el.color : theme.text,
            textAlign: el.align ?? 'left',
            fontSize: (el.font_size * 25.4 / 72 / pageW * 100) + 'cqw'
        });
    }

    function inspect() {
        const theme = resolveTheme();
        const qr = selected.type === 'qr';
        const isImage = selected.type === 'image';
        const isText = !qr && !isImage;
        const defaults = {font: 'sans', align: 'left', color: theme.text};
        selection.value = selected.id;
        find('[data-fixed]').hidden = selected.type !== 'text';
        find('[data-variable]').hidden = selected.type !== 'variable';
        find('[data-text-only]').hidden = !isText;
        find('[data-height]').hidden = qr;
        find('[data-image-only]').hidden = !isImage;
        find('[data-action=delete]').hidden = qr;
        find('[data-width-label]').textContent = qr ? 'Sisi QR (mm)' : 'Lebar (mm)';
        for (const control of controls) {
            const key = control.dataset.key;
            control.value = selected[key] ?? defaults[key] ?? '';
        }
        if (elementHexInput && isText) {
            const currentColor = isHex(selected.color) ? selected.color.toLowerCase() : theme.text;
            if (document.activeElement !== elementHexInput || isHex(elementHexInput.value)) {
                elementHexInput.value = currentColor;
                elementHexInput.removeAttribute('aria-invalid');
            }
        }
    }

    function rebuild() {
        nodes.replaceChildren();
        selection.replaceChildren();
        buttons.clear();
        for (const el of layout.elements) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'sc-node';
            button.dataset.id = el.id;
            if (el.type === 'qr') {
                button.dataset.qrNode = '';
                const image = new Image();
                image.alt = '';
                image.src = root.dataset.qr;
                const grip = document.createElement('span');
                grip.className = 'sc-qr-grip';
                grip.title = 'Geser untuk mengubah ukuran QR';
                grip.setAttribute('aria-hidden', 'true');
                button.append(image, grip);
            }
            if (el.type === 'image') {
                button.dataset.imageNode = '';
                const image = new Image();
                image.alt = '';
                button.append(image);
            }
            buttons.set(el.id, button);
            nodes.append(button);
            selection.add(new Option(captions(el), el.id));
            paint(el);
        }
        inspect();
    }

    function select(id) {
        if (find('#sc-elements').hidden) activateTab(find('#sc-elements-tab'));
        buttons.get(selected.id).setAttribute('aria-pressed', 'false');
        selected = layout.elements.find(el => el.id === id);
        buttons.get(id).setAttribute('aria-pressed', 'true');
        inspect();
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function position(x, y) {
        const tenth = v => Math.round(v * 10) / 10;
        selected.x = clamp(tenth(x), 0, pageW - selected.width);
        selected.y = clamp(tenth(y), 0, pageH - selected.height);
    }

    function pointerMm(event) {
        const rect = sheet.getBoundingClientRect();
        return {x: (event.clientX - rect.left) * pageW / rect.width, y: (event.clientY - rect.top) * pageH / rect.height};
    }

    function applyPalette(key) {
        const preset = palettes[key];
        if (!preset) return;
        layout.theme = {
            palette: key,
            background: preset.background,
            accent: preset.accent,
            frame: preset.frame,
        };
        for (const el of layout.elements) {
            if (el.type === 'qr' || el.type === 'image') continue;
            const current = isHex(el.color) ? el.color.toLowerCase() : null;
            if (!current || paletteTextRoles.text.has(current)) {
                el.color = preset.text;
            } else if (paletteTextRoles.muted.has(current)) {
                el.color = preset.muted;
            } else if (paletteTextRoles.accent_text.has(current)) {
                el.color = preset.accent_text;
            }
        }
        paper();
        inspect();
        changed();
    }

    function updateThemeColor(key, hex) {
        const themeObj = ensureThemeObject();
        themeObj[key] = hex.toLowerCase();
        const resolved = resolveTheme();
        themeObj.palette = resolved.palette;
        paper();
        inspect();
        changed();
    }

    for (const btn of paletteButtons) {
        btn.addEventListener('click', () => applyPalette(btn.dataset.palette));
    }

    for (const key of ['background', 'accent', 'frame']) {
        const colorInput = find('[data-theme-color=' + key + ']');
        const hexInput = find('[data-theme-hex=' + key + ']');
        colorInput.addEventListener('input', () => {
            hexInput.value = colorInput.value.toLowerCase();
            hexInput.removeAttribute('aria-invalid');
            updateThemeColor(key, colorInput.value);
        });
        hexInput.addEventListener('input', () => {
            const val = hexInput.value.trim();
            if (isHex(val)) {
                hexInput.removeAttribute('aria-invalid');
                updateThemeColor(key, val);
            } else {
                hexInput.setAttribute('aria-invalid', 'true');
            }
        });
        hexInput.addEventListener('blur', () => {
            if (!isHex(hexInput.value.trim())) {
                const resolved = resolveTheme();
                hexInput.value = resolved[key];
                hexInput.removeAttribute('aria-invalid');
            }
        });
    }

    if (elementHexInput) {
        elementHexInput.addEventListener('input', () => {
            if (selected.type === 'qr' || selected.type === 'image') return;
            const val = elementHexInput.value.trim();
            if (isHex(val)) {
                elementHexInput.removeAttribute('aria-invalid');
                selected.color = val.toLowerCase();
                find('[data-key=color]').value = selected.color;
                paint(selected);
                updateContrastNotice(resolveTheme());
                changed();
            } else {
                elementHexInput.setAttribute('aria-invalid', 'true');
            }
        });
        elementHexInput.addEventListener('blur', () => {
            if (selected.type === 'qr' || selected.type === 'image') return;
            if (!isHex(elementHexInput.value.trim())) {
                const theme = resolveTheme();
                elementHexInput.value = isHex(selected.color) ? selected.color.toLowerCase() : theme.text;
                elementHexInput.removeAttribute('aria-invalid');
            }
        });
    }

    selection.addEventListener('change', () => select(selection.value));
    nodes.addEventListener('click', event => {
        const button = event.target.closest('.sc-node');
        if (button) select(button.dataset.id);
    });
    nodes.addEventListener('keydown', event => {
        const directions = {ArrowLeft: [-1, 0], ArrowRight: [1, 0], ArrowUp: [0, -1], ArrowDown: [0, 1]};
        if (work.disabled || event.ctrlKey || event.altKey || event.metaKey || !directions[event.key]) return;
        const button = event.target.closest('.sc-node');
        if (!button) return;
        event.preventDefault();
        select(button.dataset.id);
        const [dx, dy] = directions[event.key];
        const step = event.shiftKey ? 5 : 1;
        position(Number(selected.x) + dx * step, Number(selected.y) + dy * step);
        paint(selected);
        inspect();
        changed();
    });
    nodes.addEventListener('pointerdown', event => {
        const button = event.target.closest('.sc-node');
        if (work.disabled || !button || !event.isPrimary || event.button !== 0) return;
        select(button.dataset.id);
        const point = pointerMm(event);
        drag = {
            id: event.pointerId, resize: event.target.classList.contains('sc-qr-grip'),
            start: point, x: Number(selected.x), y: Number(selected.y), size: Number(selected.width)
        };
        button.setPointerCapture(event.pointerId);
    });
    nodes.addEventListener('pointermove', event => {
        if (!drag || drag.id !== event.pointerId) return;
        const point = pointerMm(event);
        const dx = point.x - drag.start.x;
        const dy = point.y - drag.start.y;
        if (drag.resize) {
            const delta = Math.abs(dx) > Math.abs(dy) ? dx : dy;
            const size = Math.round((drag.size + delta) * 10) / 10;
            selected.width = selected.height = clamp(size, 30, Math.min(pageW - drag.x, pageH - drag.y));
        } else {
            position(drag.x + dx, drag.y + dy);
        }
        paint(selected);
        inspect();
        changed();
    });
    nodes.addEventListener('lostpointercapture', () => { drag = null; });

    for (const control of controls) {
        const key = control.dataset.key;
        control.addEventListener('input', () => {
            if (control.type === 'number') {
                const n = control.valueAsNumber;
                if (!Number.isFinite(n)) return;
                if (key === 'x' || key === 'y') position(key === 'x' ? n : selected.x, key === 'y' ? n : selected.y);
                if (key === 'width' || key === 'height') {
                    if (selected.type === 'qr') selected.width = selected.height = clamp(n, 30, Math.min(pageW, pageH));
                    else selected[key] = clamp(n, 0.1, key === 'width' ? pageW : pageH);
                    position(selected.x, selected.y);
                }
                if (key === 'font_size') selected.font_size = clamp(n, 1, 200);
                for (const other of controls) {
                    if (other !== control && other.type === 'number') other.value = selected[other.dataset.key] ?? '';
                }
            } else {
                selected[key] = control.value;
                if (key === 'color' && elementHexInput) {
                    elementHexInput.value = control.value.toLowerCase();
                    elementHexInput.removeAttribute('aria-invalid');
                }
            }
            paint(selected);
            updateContrastNotice(resolveTheme());
            selection.selectedOptions[0].text = captions(selected);
            changed();
        });
        control.addEventListener('change', inspect);
        if (control.type === 'number') control.addEventListener('blur', inspect);
    }

    function appendElement(element) {
        let suffix = layout.elements.length + 1;
        while (buttons.has('elemen-' + suffix)) suffix++;
        selected = {id: 'elemen-' + suffix, x: 0, y: 0, ...element};
        position(26, 26);
        layout.elements.push(selected);
        rebuild();
        changed();
    }

    find('[data-action=add]').onclick = () => {
        const type = addType.value;
        if (Object.hasOwn(imageLabels, type)) {
            imageAction = {label: imageLabels[type]};
            imageInput.click();
            return;
        }
        appendElement({
            type: type === 'text' ? 'text' : 'variable',
            ...(type === 'text' ? {text: 'Teks baru'} : {field: type}),
            width: Math.min(95, pageW), height: 15, font_size: 14, color: resolveTheme().text,
        });
        find(type === 'text' ? '[data-key=text]' : '[data-key=field]').focus();
    };
    find('[data-action=replace-image]').onclick = () => {
        imageAction = {element: selected};
        imageInput.click();
    };
    find('[data-action=delete]').onclick = () => {
        if (selected.type === 'qr') return;
        layout.elements.splice(layout.elements.indexOf(selected), 1);
        selected = layout.elements[0];
        rebuild();
        updateContrastNotice(resolveTheme());
        changed();
        selection.focus();
    };
    find('[data-frame]').onchange = event => {
        layout.frame = event.target.checked;
        paper();
        changed();
    };
    find('[data-action=clear-bg]').onclick = () => {
        delete layout.background;
        paper();
        changed();
    };

    async function request(url, method, body) {
        const headers = {'Accept': 'application/json', 'X-CSRF-TOKEN': root.dataset.token};
        if (!(body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(body);
        }
        const response = await fetch(url, {method, headers, body});
        if (!response.ok) {
            const details = await response.json().catch(() => ({}));
            const errors = Object.values(details.errors ?? {}).flat();
            throw new Error(errors.join(' ') || details.message || 'Permintaan gagal (HTTP ' + response.status + ').');
        }
        return response;
    }

    async function run(action) {
        if (work.disabled) return;
        work.disabled = true;
        work.setAttribute('aria-busy', 'true');
        try {
            await action();
        } catch (error) {
            message(error.message, true);
        } finally {
            work.removeAttribute('aria-busy');
            work.disabled = false;
        }
    }

    async function uploadImage(file) {
        const body = new FormData();
        body.append('background', file);
        const response = await request(root.dataset.upload, 'POST', body);
        return (await response.json()).background;
    }

    find('[data-upload-file]').onchange = event => {
        const file = event.target.files[0];
        event.target.value = '';
        if (!file) return;
        run(async () => {
            layout.background = await uploadImage(file);
            paper();
            changed();
        });
    };
    imageInput.onchange = event => {
        const file = event.target.files[0];
        const action = imageAction;
        event.target.value = '';
        imageAction = null;
        if (!file || !action) return;
        run(async () => {
            const image = await uploadImage(file);
            if (action.element) {
                action.element.image = image;
                paint(action.element);
                changed();
                return;
            }
            appendElement({type: 'image', label: action.label, image, width: Math.min(38, pageW), height: 22});
        });
    };
    find('[data-action=preview]').onclick = () => run(async () => {
        const response = await request(root.dataset.preview, 'POST', {layout});
        previewUrl = URL.createObjectURL(await response.blob());
        find('iframe').src = previewUrl;
        find('[data-preview-link]').href = previewUrl;
        dialog.showModal();
    });
    find('[data-close]').onclick = () => dialog.close();
    dialog.addEventListener('close', () => {
        find('iframe').removeAttribute('src');
        find('[data-preview-link]').removeAttribute('href');
        URL.revokeObjectURL(previewUrl);
        previewUrl = null;
    });
    find('[data-action=save]').onclick = () => run(async () => {
        await request(root.dataset.save, 'PUT', {layout});
        dirty = false;
        message('Desain tersimpan.');
    });
    window.addEventListener('beforeunload', event => {
        if (dirty) { event.preventDefault(); event.returnValue = ''; }
    });
    paper();
    rebuild();
})();
