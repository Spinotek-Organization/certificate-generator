(() => {
    const root = document.currentScript.closest('.sc-editor');
    const find = selector => root.querySelector(selector);
    const layout = JSON.parse(find('[data-layout]').textContent);
    const sample = JSON.parse(find('[data-sample]').textContent);
    const sheet = find('.sc-sheet');
    const nodes = find('.sc-nodes');
    const selection = find('[data-selection]');
    const controls = [...root.querySelectorAll('[data-key]')];
    const status = find('.sc-status');
    const dialog = find('dialog');
    const work = find('.sc-work');
    const {width: pageW, height: pageH} = layout.page;
    const fields = {participant_name: 'Nama peserta', activity_title: 'Nama kegiatan', issuer: 'Penerbit', issued_date: 'Tanggal terbit', status: 'Status'};
    const defaults = {font: 'sans', align: 'left', color: '#243d35'};
    const fonts = {sans: '"DejaVu Sans", system-ui, sans-serif', serif: '"DejaVu Serif", Georgia, serif', mono: '"DejaVu Sans Mono", monospace'};
    const buttons = new Map();
    let selected = layout.elements[0];
    let drag = null;
    let previewUrl = null;
    let dirty = false;

    find('[data-add-type]').add(new Option('Teks tetap', 'text'));
    for (const [field, label] of Object.entries(fields)) {
        find('[data-add-type]').add(new Option(label, field));
        find('[data-key=field]').add(new Option(label, field));
    }
    sheet.style.aspectRatio = pageW + ' / ' + pageH;
    find('.sc-frame').style.inset = (800 / pageH) + '% ' + (800 / pageW) + '%';

    function message(text, error = false) {
        status.textContent = text;
        status.toggleAttribute('data-error', error);
    }

    function changed() {
        dirty = true;
        message('Perubahan belum disimpan.');
    }

    function captions(el) {
        if (el.type === 'qr') return 'QR verifikasi';
        return el.type === 'variable' ? fields[el.field] : el.text || 'Teks kosong';
    }

    function paper() {
        const image = find('.sc-bg');
        if (layout.background) image.src = layout.background;
        else image.removeAttribute('src');
        image.hidden = !layout.background;
        find('[data-action=clear-bg]').hidden = !layout.background;
        find('[data-frame]').checked = layout.frame ?? true;
        find('.sc-frame').hidden = !find('[data-frame]').checked;
    }

    function paint(el) {
        const button = buttons.get(el.id);
        Object.assign(button.style, {
            left: (el.x / pageW * 100) + '%', top: (el.y / pageH * 100) + '%',
            width: (el.width / pageW * 100) + '%', height: (el.height / pageH * 100) + '%'
        });
        button.setAttribute('aria-label', captions(el));
        button.setAttribute('aria-pressed', String(el === selected));
        if (el.type === 'qr') return;
        button.textContent = el.type === 'text' ? el.text : sample[el.field];
        Object.assign(button.style, {
            fontFamily: fonts[el.font] ?? fonts.sans, color: el.color ?? defaults.color,
            textAlign: el.align ?? defaults.align, fontSize: (el.font_size * 25.4 / 72 / pageW * 100) + 'cqw'
        });
    }

    function inspect() {
        const qr = selected.type === 'qr';
        selection.value = selected.id;
        find('[data-fixed]').hidden = selected.type !== 'text';
        find('[data-variable]').hidden = selected.type !== 'variable';
        for (const group of root.querySelectorAll('[data-text-only]')) group.hidden = qr;
        find('[data-action=delete]').hidden = qr;
        find('[data-width-label]').textContent = qr ? 'Sisi QR (mm)' : 'Lebar (mm)';
        for (const control of controls) {
            const key = control.dataset.key;
            control.value = selected[key] ?? defaults[key] ?? '';
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
            buttons.set(el.id, button);
            nodes.append(button);
            selection.add(new Option(captions(el), el.id));
            paint(el);
        }
        inspect();
    }

    function select(id) {
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

    selection.addEventListener('change', () => select(selection.value));
    nodes.addEventListener('click', event => {
        const button = event.target.closest('.sc-node');
        if (button) select(button.dataset.id);
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
            }
            paint(selected);
            selection.selectedOptions[0].text = captions(selected);
            changed();
        });
        control.addEventListener('change', inspect);
        if (control.type === 'number') control.addEventListener('blur', inspect);
    }

    find('[data-action=add]').onclick = () => {
        const type = find('[data-add-type]').value;
        let suffix = layout.elements.length + 1;
        while (buttons.has('elemen-' + suffix)) suffix++;
        selected = {id: 'elemen-' + suffix, type: type === 'text' ? 'text' : 'variable',
            x: 5, y: 5, width: Math.min(95, pageW - 5), height: 15, font_size: 14};
        if (type === 'text') selected.text = 'Teks baru';
        else selected.field = type;
        layout.elements.push(selected);
        rebuild();
        changed();
        find(type === 'text' ? '[data-key=text]' : '[data-key=field]').focus();
    };
    find('[data-action=delete]').onclick = () => {
        if (selected.type === 'qr') return;
        layout.elements.splice(layout.elements.indexOf(selected), 1);
        selected = layout.elements[0];
        rebuild();
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
        try {
            await action();
        } catch (error) {
            message(error.message, true);
        } finally {
            work.disabled = false;
        }
    }

    find('[data-upload-file]').onchange = event => {
        const file = event.target.files[0];
        event.target.value = '';
        if (!file) return;
        run(async () => {
            const body = new FormData();
            body.append('background', file);
            const response = await request(root.dataset.upload, 'POST', body);
            layout.background = (await response.json()).background;
            paper();
            changed();
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
