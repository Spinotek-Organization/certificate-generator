body:has(> .sc-editor) { margin: 0; background: #f3efe7; }
.sc-editor {
    --ink: #14233c;
    --muted: #48566a;
    --surface: #ffffff;
    --canvas-bg: #f3efe7;
    --stage-bg: #e7e1d5;
    --edge: #cec6b8;
    --control-edge: #756f64;
    --accent: #a27d34;
    --focus: #9a5812;
    --danger: #8b261d;
    font: 1rem/1.45 system-ui, -apple-system, sans-serif;
    color: var(--ink);
    background: var(--canvas-bg);
    padding: clamp(.9rem, 2.4vw, 2.25rem);
    min-height: 100dvh;
}
.sc-editor *, .sc-editor *::before, .sc-editor *::after { box-sizing: border-box; }
.sc-editor [hidden] { display: none !important; }
.sc-editor .sc-work { min-width: 0; border: 0; padding: 0; margin: 0 auto; max-width: 92rem; }
.sc-editor h1, .sc-editor h2, .sc-editor p { margin: 0; }
.sc-editor h1 { font: normal clamp(1.75rem, 3.2vw, 2.55rem)/1.12 Georgia, "DejaVu Serif", serif; letter-spacing: -.01em; }
.sc-editor h2 { font-size: 1.05rem; font-weight: 650; letter-spacing: -.005em; }
.sc-editor .sc-title { display: grid; gap: .25rem; }
.sc-editor .sc-title p { color: var(--muted); font-size: .92rem; }
.sc-editor .sc-head,
.sc-editor .sc-actions,
.sc-editor .sc-sheet-info,
.sc-editor .sc-sheet-meta,
.sc-editor .sc-panel-head,
.sc-editor .sc-paper-options,
.sc-editor .sc-preview > div { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
.sc-editor .sc-head,
.sc-editor .sc-sheet-info,
.sc-editor .sc-panel-head { justify-content: space-between; }
.sc-editor .sc-head { border-bottom: 1px solid var(--edge); padding: .25rem 0 1.1rem; margin-bottom: 1rem; }
.sc-editor .sc-workspace { display: grid; align-items: start; grid-template-columns: minmax(0, 1fr) minmax(18.5rem, 22rem); gap: clamp(1rem, 2.2vw, 1.85rem); }
.sc-editor .sc-canvas-panel, .sc-editor .sc-properties { min-width: 0; }
.sc-editor .sc-canvas-panel { display: grid; gap: 1rem; }
.sc-editor .sc-sheet-info { font-size: .88rem; color: var(--muted); }
.sc-editor .sc-sheet-info strong { color: var(--ink); font-size: .95rem; }
.sc-editor .sc-palette-tag { background: var(--surface); border: 1px solid var(--edge); border-radius: .25rem; padding: .15rem .55rem; font-size: .82rem; font-weight: 600; color: var(--ink); }
.sc-editor .sc-sheet-stage { background: var(--stage-bg); border: 1px solid var(--edge); border-radius: .35rem; padding: clamp(.5rem, 1.5vw, 1.25rem); }
.sc-editor .sc-sheet { container-type: inline-size; position: relative; isolation: isolate; width: 100%; background: #ffffff; box-shadow: 0 10px 28px rgb(20 35 60 / 12%); }
.sc-editor .sc-bg, .sc-editor .sc-frame, .sc-editor .sc-nodes { position: absolute; inset: 0; width: 100%; height: 100%; }
.sc-editor .sc-bg { object-fit: fill; }
.sc-editor .sc-frame { pointer-events: none; }
.sc-editor .sc-frame-part { position: absolute; }
.sc-editor button, .sc-editor select, .sc-editor input, .sc-editor textarea { font: inherit; color: inherit; }
.sc-editor button,
.sc-editor select,
.sc-editor input:not([type=checkbox]),
.sc-editor textarea { border: 1px solid var(--control-edge); border-radius: .3rem; background: var(--surface); padding: .5rem .7rem; min-height: 2.75rem; }
.sc-editor button,
.sc-editor select,
.sc-editor input[type=color],
.sc-editor input[type=checkbox],
.sc-editor label[for],
.sc-editor .sc-check { cursor: pointer; }
.sc-editor button { font-weight: 550; transition: background-color 140ms ease, border-color 140ms ease, color 140ms ease; }
.sc-editor button:hover:not(:disabled) { background: #f6f2ea; border-color: var(--ink); }
.sc-editor button:active:not(:disabled) { background: #ebe4d7; }
.sc-editor button:disabled { cursor: not-allowed; opacity: .65; }
.sc-editor .sc-primary { background: var(--ink); border-color: var(--ink); color: #faf6ee; }
.sc-editor .sc-primary:hover:not(:disabled) { background: #1e3354; border-color: #1e3354; color: #faf6ee; }
.sc-editor .sc-primary:active:not(:disabled) { background: #0f1b2e; }
.sc-editor :is(button, input, select, textarea, a):focus-visible { outline: 3px solid var(--focus); outline-offset: 2px; }
.sc-editor .sc-node { all: unset; box-sizing: border-box; position: absolute; display: flex; flex-direction: column; justify-content: flex-start; overflow: hidden; touch-action: none; user-select: none; cursor: grab; white-space: pre-wrap; overflow-wrap: anywhere; line-height: 1.15; }
.sc-editor .sc-node:hover, .sc-editor .sc-node[aria-pressed=true], .sc-editor .sc-node:focus-visible { outline: 2px solid var(--focus); outline-offset: 1px; }
.sc-editor .sc-node:active { cursor: grabbing; }
.sc-editor .sc-node img { display: block; width: 100%; height: 100%; pointer-events: none; }
.sc-editor .sc-node[data-image-node] img { object-fit: contain; }
.sc-editor .sc-node[data-qr-node] img { background: #ffffff; }
.sc-editor .sc-node[data-qr-node] { z-index: 1; }
.sc-editor .sc-image-controls { display: grid; gap: .75rem; }
.sc-editor .sc-image-controls button { justify-self: start; }
.sc-editor .sc-sheet-meta a { color: var(--ink); text-underline-offset: .18em; }
.sc-editor .sc-qr-grip { display: none; position: absolute; bottom: 0; right: 0; width: 1.1rem; height: 1.1rem; border-right: 4px solid var(--focus); border-bottom: 4px solid var(--focus); cursor: nwse-resize; }
.sc-editor .sc-node[aria-pressed=true] .sc-qr-grip { display: block; }
.sc-editor .sc-theme-panel,
.sc-editor .sc-properties { background: var(--surface); border: 1px solid var(--edge); border-radius: .35rem; padding: clamp(.9rem, 1.8vw, 1.25rem); display: grid; gap: .9rem; }
.sc-editor .sc-hint { font-size: .82rem; color: var(--muted); }
.sc-editor .sc-palettes { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 14rem), 1fr)); gap: .65rem; }
.sc-editor .sc-palette-btn { display: flex; align-items: center; gap: .75rem; text-align: left; padding: .6rem .8rem; min-height: 3rem; border-color: var(--control-edge); background: var(--surface); }
.sc-editor .sc-palette-btn[aria-pressed=true] { border-color: var(--ink); border-width: 2px; background: #f8f4ec; }
.sc-editor .sc-swatches { display: inline-flex; flex-shrink: 0; border-radius: .2rem; overflow: hidden; border: 1px solid var(--control-edge); }
.sc-editor .sc-swatches span { width: 1.15rem; height: 1.65rem; display: block; }
.sc-editor .sc-palette-name { font-size: .9rem; font-weight: 600; line-height: 1.25; }
.sc-editor .sc-theme-colors { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; }
.sc-editor .sc-color-field { display: grid; gap: .3rem; min-width: 0; align-content: start; }
.sc-editor .sc-color-field > label,
.sc-editor label { display: grid; gap: .3rem; min-width: 0; font-size: .86rem; font-weight: 600; color: var(--ink); }
.sc-editor .sc-color-inputs { display: grid; grid-template-columns: 2.85rem minmax(0, 1fr); gap: .4rem; align-items: center; }
.sc-editor input[type=color] { padding: .2rem; width: 2.85rem; min-width: 2.85rem; }
.sc-editor input[data-theme-hex],
.sc-editor input[data-key-hex] { font-family: ui-monospace, "DejaVu Sans Mono", monospace; font-size: .88rem; text-transform: lowercase; }
.sc-editor input[aria-invalid=true] { border-color: var(--danger); background: #fdf3f2; }
.sc-editor .sc-contrast-note { font-size: .85rem; color: var(--danger); background: #fdf3f2; border: 1px solid #d9aba6; border-radius: .25rem; padding: .5rem .7rem; }
.sc-editor :is(select, textarea, input:not([type=checkbox]):not([type=color])) { width: 100%; min-width: 0; }
.sc-editor .sc-paper-options { align-items: end; gap: .85rem 1.1rem; }
.sc-editor .sc-paper-options small { font-weight: 400; color: var(--muted); }
.sc-editor .sc-upload-field { display: grid; gap: .3rem; flex: 1 1 14rem; }
.sc-editor .sc-upload-field input[type=file] { padding: .42rem .55rem; max-width: 22rem; }
.sc-editor .sc-check { display: inline-flex; align-items: center; gap: .5rem; min-height: 2.75rem; font-weight: 600; }
.sc-editor .sc-check input { width: 1.15rem; height: 1.15rem; accent-color: var(--ink); }
.sc-editor .sc-pair, .sc-editor .sc-add { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: .65rem; }
.sc-editor .sc-add { grid-template-columns: minmax(0, 1fr) auto; align-items: end; }
.sc-editor hr { width: 100%; margin: .1rem 0; border: 0; border-top: 1px solid var(--edge); }
.sc-editor .sc-delete { justify-self: start; color: var(--danger); border-color: #c99993; }
.sc-editor .sc-delete:hover:not(:disabled) { background: #fdf3f2; border-color: var(--danger); }
.sc-editor .sc-status { min-height: 1.4rem; margin: 0 0 .8rem; font-size: .9rem; font-weight: 600; color: var(--ink); }
.sc-editor .sc-status:empty { min-height: 0; margin-bottom: .35rem; }
.sc-editor .sc-status[data-error] { color: var(--danger); }
.sc-editor .sc-preview { position: fixed; inset: 0; margin: auto; padding: 1rem; width: min(92vw, 72rem); max-width: 96vw; border: 1px solid var(--edge); border-radius: .35rem; background: var(--surface); color: var(--ink); }
.sc-editor .sc-preview::backdrop { background: rgb(16 26 42 / 60%); }
.sc-editor .sc-preview a { margin-left: auto; color: var(--ink); font-weight: 600; text-decoration: underline; padding: .4rem .25rem; }
.sc-editor .sc-preview iframe { display: block; margin-top: .75rem; width: 100%; height: 74vh; border: 1px solid var(--edge); }
@media (prefers-reduced-motion: reduce) {
    .sc-editor button { transition: none; }
}
@media (max-width: 68rem) {
    .sc-editor .sc-workspace { grid-template-columns: 1fr; }
    .sc-editor .sc-properties { grid-template-columns: repeat(auto-fit, minmax(min(100%, 16rem), 1fr)); align-items: start; }
    .sc-editor .sc-properties h2, .sc-editor .sc-properties hr, .sc-editor .sc-delete { grid-column: 1 / -1; }
}
@media (max-width: 42rem) {
    .sc-editor { padding: .75rem; }
    .sc-editor .sc-head { gap: .75rem; }
    .sc-editor .sc-actions { width: 100%; }
    .sc-editor .sc-actions button { flex: 1 1 8.5rem; }
    .sc-editor .sc-theme-colors { grid-template-columns: 1fr; }
    .sc-editor .sc-pair { grid-template-columns: 1fr; }
    .sc-editor .sc-sheet-stage { padding: .45rem; }
}
