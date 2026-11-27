body:has(> .sc-editor) { margin: 0; background: #f6f7fb; }

.sc-editor {
    --ink: #111827;
    --muted: #6b7280;
    --surface: #ffffff;
    --canvas-bg: #f6f7fb;
    --border: #eaedf2;
    --control-border: #e2e6ee;
    --focus: #0066ff;
    --focus-dark: #0052e0;
    --danger: #dc2626;
    min-height: 100dvh;
    display: flex;
    flex-direction: column;
    color: var(--ink);
    background: var(--canvas-bg);
    font: .88rem/1.45 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    -webkit-font-smoothing: antialiased;
}

.sc-editor *, .sc-editor *::before, .sc-editor *::after { box-sizing: border-box; }
.sc-editor [hidden] { display: none !important; }
.sc-editor .sc-work { min-width: 0; border: 0; padding: 0; margin: 0; flex: 1; display: flex; flex-direction: column; }
.sc-editor h1, .sc-editor h2, .sc-editor p { margin: 0; }
.sc-editor h1 { font-size: .95rem; font-weight: 650; letter-spacing: -.015em; line-height: 1.2; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 14rem; }
.sc-editor h2 { font-size: .84rem; font-weight: 650; color: #0f172a; letter-spacing: -.01em; }

/* Top Bar (3-zone white header matching reference) */
.sc-editor .sc-head {
    color: var(--ink);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
}
.sc-editor .sc-head-inner {
    width: 100%;
    padding: .42rem 1.15rem;
    min-height: 3.05rem;
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: .75rem;
}
.sc-editor .sc-title { display: flex; align-items: center; gap: .5rem; min-width: 0; }
.sc-editor .sc-brand {
    color: #0f172a;
    font-weight: 700;
    font-size: .9rem;
    padding-right: .55rem;
    border-right: 1px solid var(--border);
}
.sc-editor .sc-logo-badge {
    width: 1.55rem;
    height: 1.55rem;
    background: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sc-editor .sc-logo-img { width: 100%; height: 100%; object-fit: contain; display: block; }

.sc-editor .sc-topnav { display: flex; align-items: center; gap: .3rem; }
.sc-editor .sc-topnav a,
.sc-editor .sc-topnav-active {
    padding: .34rem .78rem;
    border-radius: .48rem;
    font-size: .82rem;
    font-weight: 550;
    color: var(--muted);
    text-decoration: none;
}
.sc-editor .sc-topnav a:hover { background: #f3f5f8; color: var(--ink); }
.sc-editor .sc-topnav-active { background: #f3f5f8; color: var(--ink); font-weight: 600; }

.sc-editor .sc-actions { display: flex; align-items: center; justify-content: flex-end; gap: .4rem; flex-wrap: wrap; }
.sc-editor .sc-actions button,
.sc-editor .sc-head-link {
    display: inline-flex;
    align-items: center;
    padding: .34rem .78rem;
    min-height: 2rem;
    font-size: .82rem;
    border: 1px solid var(--control-border);
    border-radius: .48rem;
    background: var(--surface);
    color: #374151;
    font-weight: 550;
    text-decoration: none;
}
.sc-editor .sc-head-link { display: none; }
.sc-editor .sc-actions button:hover:not(:disabled),
.sc-editor .sc-head-link:hover { background: #f8fafc; border-color: #cbd5e1; color: var(--ink); }
.sc-editor .sc-actions .sc-primary {
    background: var(--focus);
    border-color: var(--focus);
    color: #ffffff;
    font-weight: 600;
    padding-inline: .9rem;
}
.sc-editor .sc-actions .sc-primary:hover:not(:disabled) {
    background: var(--focus-dark);
    border-color: var(--focus-dark);
    color: #ffffff;
}

/* 3-Column App Shell */
.sc-editor .sc-workspace {
    flex: 1;
    display: grid;
    grid-template-columns: 13.75rem minmax(0, 1fr) 20.5rem;
    align-items: stretch;
    width: 100%;
}

/* Left Sidebar */
.sc-editor .sc-left-sidebar {
    background: var(--surface);
    border-right: 1px solid var(--border);
    padding: .95rem .75rem;
    display: flex;
    flex-direction: column;
    gap: 1.15rem;
    min-width: 0;
}
.sc-editor .sc-side-group { display: grid; gap: .2rem; }
.sc-editor .sc-side-label {
    padding: 0 .6rem .25rem;
    font-size: .72rem;
    font-weight: 650;
    color: var(--muted);
}
.sc-editor .sc-side-scroll {
    display: grid;
    gap: .2rem;
    max-height: calc(100dvh - 22rem);
    overflow-y: auto;
}
.sc-editor .sc-side-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .45rem;
    padding: .44rem .65rem;
    border-radius: .48rem;
    font-size: .81rem;
    font-weight: 500;
    color: #4b5563;
    text-decoration: none;
    transition: background-color 120ms ease, color 120ms ease;
}
.sc-editor .sc-side-link:hover { background: #f3f5f8; color: var(--ink); }
.sc-editor .sc-side-link.is-active { background: #0f172a; color: #ffffff; font-weight: 600; }
.sc-editor .sc-side-item-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sc-editor .sc-side-cat {
    font-size: .68rem;
    color: #6b7280;
    background: #f3f5f8;
    padding: .05rem .38rem;
    border-radius: 999px;
    flex-shrink: 0;
}
.sc-editor .sc-side-link.is-active .sc-side-cat { background: rgb(255 255 255 / 18%); color: #ffffff; }

.sc-editor .sc-checklist {
    margin-top: auto;
    padding-top: .85rem;
    border-top: 1px solid var(--border);
    display: grid;
    gap: .42rem;
}
.sc-editor .sc-checklist-head { font-size: .76rem; font-weight: 650; color: #0f172a; padding-bottom: .15rem; }
.sc-editor .sc-check-item { display: flex; align-items: center; gap: .45rem; font-size: .76rem; color: #4b5563; }
.sc-editor .sc-check-dot {
    width: 1rem;
    height: 1rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .65rem;
    font-weight: 700;
    background: #e5e7eb;
    color: #4b5563;
    flex-shrink: 0;
}
.sc-editor .sc-check-dot.is-done { background: #0f172a; color: #ffffff; }

/* Center Canvas */
.sc-editor .sc-canvas-panel {
    min-width: 0;
    padding: .6rem 1rem .75rem;
    background: var(--canvas-bg);
    display: flex;
    flex-direction: column;
}
.sc-editor .sc-sheet-info,
.sc-editor .sc-sheet-meta,
.sc-editor .sc-preview > div { display: flex; align-items: center; gap: .5rem; }
.sc-editor .sc-sheet-info {
    padding: .1rem .15rem .45rem;
    justify-content: space-between;
    flex-wrap: wrap;
}
.sc-editor .sc-sheet-title { display: flex; align-items: baseline; gap: .45rem; }
.sc-editor .sc-sheet-title > span, .sc-editor .sc-canvas-tip { color: var(--muted); font-size: .76rem; }
.sc-editor .sc-palette-tag {
    display: inline-flex;
    gap: .42rem;
    align-items: center;
    text-align: left;
    color: #374151;
    background: var(--surface);
    border: 1px solid var(--control-border);
    min-height: 1.85rem;
    padding: .2rem .6rem;
    font-size: .76rem;
    font-weight: 550;
}
.sc-editor .sc-palette-tag:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; }
.sc-editor .sc-palette-dot { width: .52rem; height: .52rem; flex: 0 0 .52rem; border-radius: 50%; background: var(--focus); }
.sc-editor .sc-chevron { width: .34rem; height: .34rem; border-right: 1.2px solid; border-bottom: 1.2px solid; transform: rotate(45deg); margin: -.18rem .05rem 0 .14rem; }

.sc-editor .sc-sheet-stage {
    flex: 1;
    display: grid;
    place-items: center;
    padding: .35rem .5rem;
    min-width: 0;
}
.sc-editor .sc-sheet {
    position: relative;
    isolation: isolate;
    width: min(100%, clamp(22rem, calc((100svh - 10.5rem) * var(--paper-ratio, 1.414)), 58rem));
    background: white;
    box-shadow: 0 10px 28px rgb(15 23 42 / 7%), 0 1px 3px rgb(15 23 42 / 5%);
}
.sc-editor .sc-canvas-tip { padding: .35rem .25rem 0; text-align: center; }
.sc-editor .sc-bg, .sc-editor .sc-frame, .sc-editor .sc-nodes { position: absolute; inset: 0; width: 100%; height: 100%; }
.sc-editor .sc-nodes { container-type: inline-size; }
.sc-editor .sc-bg { object-fit: fill; }
.sc-editor .sc-frame { pointer-events: none; }
.sc-editor .sc-frame-part { position: absolute; }

/* Right Inspector Floating Card (like "Thread" panel in reference) */
.sc-editor .sc-inspector {
    min-width: 0;
    margin: .65rem .85rem .75rem 0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: .75rem;
    box-shadow: 0 1px 2px rgb(16 24 40 / 3%);
    overflow-y: auto;
    max-height: calc(100dvh - 4.45rem);
}

.sc-editor button, .sc-editor select, .sc-editor input, .sc-editor textarea { font: inherit; color: inherit; }
.sc-editor button, .sc-editor select, .sc-editor input:not([type=checkbox]), .sc-editor textarea {
    border: 1px solid var(--control-border);
    border-radius: .48rem;
    background: var(--surface);
    padding: .44rem .62rem;
    min-height: 2.3rem;
    transition: background-color 120ms ease, border-color 120ms ease, box-shadow 120ms ease;
}
.sc-editor :is(button, select, input[type=color], input[type=checkbox], label[for]) { cursor: pointer; }
.sc-editor button { font-weight: 550; }
.sc-editor button:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; }
.sc-editor button:disabled { cursor: wait; opacity: .65; }
.sc-editor :is(button, input, select, textarea, a):focus-visible { outline: 2px solid rgb(0 102 255 / 45%); outline-offset: 1px; }

.sc-editor .sc-node {
    all: unset;
    box-sizing: border-box;
    position: absolute;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    overflow: visible;
    touch-action: none;
    user-select: none;
    cursor: grab;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    line-height: 1.15;
}
.sc-editor .sc-node-content { width: 100%; height: 100%; overflow: hidden; pointer-events: none; }
.sc-editor .sc-node:hover, .sc-editor .sc-node[aria-pressed=true], .sc-editor .sc-node:focus-visible {
    outline: 1.5px solid #0066ffcc;
    outline-offset: 1px;
    border-radius: 2px;
}
.sc-editor .sc-node:focus-visible { outline: 2px solid var(--focus); }
.sc-editor button.sc-node:hover:not(:disabled) { background: transparent; }
.sc-editor .sc-node:active { cursor: grabbing; }
.sc-editor .sc-node img { display: block; width: 100%; height: 100%; pointer-events: none; }
.sc-editor .sc-node[data-image-node] img { object-fit: contain; }
.sc-editor .sc-node[data-qr-node] img { background: white; }
.sc-editor .sc-node[data-qr-node] { z-index: 1; }

.sc-editor .sc-handle {
    display: none;
    position: absolute;
    width: .58rem;
    height: .58rem;
    background: #ffffff;
    border: 1.5px solid var(--focus);
    border-radius: 2px;
    z-index: 5;
}
.sc-editor .sc-node[aria-pressed=true] .sc-handle { display: block; }
.sc-editor .sc-handle[data-handle=nw] { top: -.3rem; left: -.3rem; cursor: nwse-resize; }
.sc-editor .sc-handle[data-handle=ne] { top: -.3rem; right: -.3rem; cursor: nesw-resize; }
.sc-editor .sc-handle[data-handle=sw] { bottom: -.3rem; left: -.3rem; cursor: nesw-resize; }
.sc-editor .sc-handle[data-handle=se] { bottom: -.3rem; right: -.3rem; cursor: nwse-resize; }
.sc-editor .sc-handle[data-handle=n]  { top: -.3rem; left: calc(50% - .29rem); cursor: ns-resize; }
.sc-editor .sc-handle[data-handle=s]  { bottom: -.3rem; left: calc(50% - .29rem); cursor: ns-resize; }
.sc-editor .sc-handle[data-handle=w]  { left: -.3rem; top: calc(50% - .29rem); cursor: ew-resize; }
.sc-editor .sc-handle[data-handle=e]  { right: -.3rem; top: calc(50% - .29rem); cursor: ew-resize; }

.sc-editor .sc-tabs {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    padding: .35rem .85rem 0;
    gap: .2rem;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 4;
}
.sc-editor .sc-tabs button {
    border: 0;
    border-radius: .35rem .35rem 0 0;
    background: transparent;
    color: var(--muted);
    padding: .42rem .3rem .52rem;
    min-height: 2.15rem;
    font-size: .8rem;
    font-weight: 550;
}
.sc-editor .sc-tabs button:hover:not(:disabled) { background: #f3f5f8; color: var(--ink); }
.sc-editor .sc-tabs button[aria-selected=true] {
    background: transparent;
    color: var(--focus);
    font-weight: 650;
    box-shadow: inset 0 -2px 0 var(--focus);
}
.sc-editor .sc-theme-panel, .sc-editor .sc-properties { padding: .9rem; display: grid; gap: .75rem; }
.sc-editor .sc-group-heading { padding-top: .75rem; border-top: 1px solid #f0f2f6; }
.sc-editor .sc-palettes { display: grid; gap: .38rem; }
.sc-editor .sc-palette-btn { display: flex; align-items: center; gap: .55rem; text-align: left; min-height: 2.45rem; background: var(--surface); }
.sc-editor .sc-palette-btn[aria-pressed=true] { border-color: var(--focus); background: #eff6ff; color: var(--focus-dark); }
.sc-editor .sc-swatches { display: inline-flex; flex-shrink: 0; border-radius: .22rem; overflow: hidden; border: 1px solid var(--border); }
.sc-editor .sc-swatches span { width: .55rem; height: 1.18rem; display: block; }
.sc-editor .sc-palette-name { font-size: .8rem; font-weight: 550; line-height: 1.25; }
.sc-editor .sc-theme-colors, .sc-editor .sc-image-controls, .sc-editor .sc-paper-options, .sc-editor .sc-custom-var-form, .sc-editor .sc-custom-var-list { display: grid; gap: .52rem; }
.sc-editor .sc-image-controls button, .sc-editor .sc-paper-options button { justify-self: start; }
.sc-editor label, .sc-editor .sc-color-field { display: grid; gap: .25rem; min-width: 0; align-content: start; }
.sc-editor label { font-size: .76rem; font-weight: 600; color: #374151; }
.sc-editor label small { font-weight: 400; color: var(--muted); }
.sc-editor .sc-color-inputs { display: grid; grid-template-columns: 2.25rem minmax(0, 1fr); gap: .35rem; align-items: center; }
.sc-editor input[type=color] { padding: .15rem; width: 2.25rem; }
.sc-editor input[data-theme-hex], .sc-editor input[data-key-hex] { font-family: ui-monospace, monospace; font-size: .76rem; text-transform: lowercase; padding-inline: .45rem; }
.sc-editor input[aria-invalid=true] { border-color: var(--danger); background: #fef2f2; }
.sc-editor .sc-contrast-note { font-size: .77rem; color: var(--danger); padding: .45rem .6rem; border: 1px solid #fecaca; border-radius: .45rem; background: #fef2f2; }
.sc-editor :is(select, textarea, input:not([type=checkbox]):not([type=color])) {
    width: 100%;
    min-width: 0;
    background: var(--surface);
    color: var(--ink);
}
.sc-editor :is(select, textarea, input:not([type=checkbox]):not([type=color])):focus {
    outline: none;
    border-color: var(--focus);
    box-shadow: 0 0 0 3px rgb(0 102 255 / 12%);
}
.sc-editor textarea { resize: vertical; }
.sc-editor .sc-paper-options small { font-weight: 400; color: var(--muted); }
.sc-editor .sc-check { display: flex; align-items: center; gap: .45rem; min-height: 2rem; cursor: pointer; color: var(--ink); }
.sc-editor .sc-check input { width: .95rem; height: .95rem; accent-color: var(--focus); }
.sc-editor .sc-pair, .sc-editor .sc-add { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: .5rem; }
.sc-editor .sc-add { grid-template-columns: minmax(0, 1fr) auto; align-items: end; }
.sc-editor [data-action=add],
.sc-editor [data-action=add-custom-var] { background: #eff6ff; border-color: #bfdbfe; color: #0052cc; font-weight: 600; }
.sc-editor [data-action=add]:hover:not(:disabled),
.sc-editor [data-action=add-custom-var]:hover:not(:disabled) { background: #dbeafe; border-color: #93c5fd; }
.sc-editor .sc-delete { justify-self: start; background: var(--surface); border-color: #f3d0ce; color: var(--danger); font-weight: 550; padding-inline: .6rem; }
.sc-editor .sc-delete:hover:not(:disabled) { background: #fef2f2; border-color: var(--danger); }
.sc-editor .sc-status { color: var(--focus-dark); font-size: .76rem; font-weight: 600; }
.sc-editor .sc-status[hidden] { display: none !important; }
.sc-editor .sc-status[data-error] { color: var(--danger); }
.sc-editor .sc-token-help { font-size: .74rem; color: var(--muted); line-height: 1.45; }
.sc-editor .sc-token-help code { background: #f3f5f8; color: #1f2937; padding: .08rem .3rem; border-radius: .25rem; font-size: .72rem; }
.sc-editor .sc-cv-item { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .42rem .6rem; border: 1px solid var(--border); border-radius: .45rem; background: #f8fafc; font-size: .78rem; }
.sc-editor .sc-cv-item button { min-height: 1.65rem; padding: .15rem .45rem; font-size: .73rem; border-color: #f3d0ce; color: var(--danger); }
.sc-editor .sc-cv-item button:hover:not(:disabled) { background: #fef2f2; border-color: var(--danger); }
.sc-editor .sc-preset-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .45rem; }
.sc-editor .sc-preset-card { display: grid; gap: .26rem; padding: .3rem; border: 1px solid var(--border); border-radius: .45rem; background: var(--surface); text-align: left; min-height: 0; }
.sc-editor .sc-preset-card img { width: 100%; aspect-ratio: 16 / 10; object-fit: cover; border-radius: .3rem; display: block; }
.sc-editor .sc-preset-card span { font-size: .72rem; font-weight: 550; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sc-editor .sc-preset-card[aria-pressed=true] { border-color: var(--focus); background: #eff6ff; color: var(--focus-dark); }

.sc-editor .sc-preview { position: fixed; inset: 0; margin: auto; padding: 1rem; width: min(94vw, 72rem); max-width: 96vw; border: 1px solid var(--border); border-radius: .85rem; background: var(--surface); color: var(--ink); box-shadow: 0 24px 60px rgb(15 23 42 / 18%); }
.sc-editor .sc-preview::backdrop { background: rgb(15 23 42 / 42%); backdrop-filter: blur(2px); }
.sc-editor .sc-preview a { margin-left: auto; color: var(--focus); font-weight: 600; padding: .35rem .2rem; text-decoration: none; }
.sc-editor .sc-preview a:hover { text-decoration: underline; }
.sc-editor .sc-preview iframe { display: block; margin-top: .75rem; width: 100%; height: 74vh; border: 1px solid var(--border); border-radius: .45rem; background: var(--canvas-bg); }

@media (max-width: 72rem) {
    .sc-editor .sc-workspace { grid-template-columns: minmax(0, 1fr) 20rem; }
    .sc-editor .sc-left-sidebar { display: none; }
    .sc-editor .sc-head-link { display: inline-flex; }
}
@media (max-width: 56rem) {
    .sc-editor .sc-head-inner { grid-template-columns: auto auto; }
    .sc-editor .sc-topnav { display: none; }
    .sc-editor .sc-workspace { grid-template-columns: 1fr; }
    .sc-editor .sc-inspector { margin: 0 .85rem 1rem; max-height: none; }
    .sc-editor .sc-sheet { width: min(100%, 44rem); }
}
@media (max-width: 34rem) {
    .sc-editor .sc-actions { width: 100%; }
    .sc-editor .sc-actions button, .sc-editor .sc-head-link { flex: 1; justify-content: center; }
    .sc-editor .sc-head-inner { padding-block: .6rem; }
}
@media (prefers-reduced-motion: reduce) {
    .sc-editor button, .sc-editor input, .sc-editor select, .sc-editor textarea { transition: none; }
}
