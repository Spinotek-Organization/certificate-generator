body:has(> .sc-editor) { margin: 0; background: #f3f2ed; }

.sc-editor {
    --ink: #1b2924;
    --muted: #58645d;
    --surface: #fffefa;
    --edge: #dbdfd6;
    --control-edge: #c3cbc3;
    --focus: #28664f;
    --danger: #963d35;
    --gutter: clamp(.8rem, 2.4vw, 2rem);
    --content: 98rem;
    min-height: 100dvh;
    color: var(--ink);
    background: #f3f2ed;
    font: .9rem/1.45 system-ui, -apple-system, sans-serif;
}

.sc-editor *, .sc-editor *::before, .sc-editor *::after { box-sizing: border-box; }
.sc-editor [hidden] { display: none !important; }
.sc-editor .sc-work { min-width: 0; border: 0; padding: 0; margin: 0; }
.sc-editor h1, .sc-editor h2, .sc-editor p { margin: 0; }
.sc-editor h1 { font: normal clamp(1.15rem, 2.5vw, 1.65rem)/1.2 Georgia, serif; }
.sc-editor h2 { font-size: .95rem; font-weight: 650; }

.sc-editor .sc-head { color: #f8f4e9; background: #14231d; border-bottom: 1px solid #0e1814; }
.sc-editor .sc-head-inner,
.sc-editor .sc-title,
.sc-editor .sc-actions,
.sc-editor .sc-sheet-info,
.sc-editor .sc-sheet-meta,
.sc-editor .sc-preview > div { display: flex; align-items: center; gap: .65rem; }

.sc-editor .sc-head-inner {
    max-width: var(--content);
    margin: auto;
    padding: .88rem var(--gutter);
    min-height: 4.65rem;
    justify-content: space-between;
    gap: .85rem 1.1rem;
    flex-wrap: wrap;
}

.sc-editor .sc-title { min-width: 0; gap: .65rem; }
.sc-editor .sc-brand {
    color: #e9d7b0;
    font-weight: 650;
    line-height: 1.15;
    padding-right: .85rem;
    border-right: 1px solid #58655a;
    margin-right: .2rem;
}
.sc-editor .sc-mark {
    display: grid;
    place-items: center;
    flex: 0 0 1.85rem;
    width: 1.85rem;
    height: 1.85rem;
    border: 1px solid #998459;
    border-radius: .4rem;
}
.sc-editor .sc-mark::before {
    content: '';
    width: .64rem;
    height: .64rem;
    border: 1px solid #ecd6a2;
    transform: rotate(45deg);
}
.sc-editor .sc-actions { flex-wrap: wrap; }
.sc-editor .sc-actions button {
    padding-inline: .95rem;
    background: #22352d;
    color: #fbf6e8;
    border-color: #5b695f;
}
.sc-editor .sc-actions button:hover:not(:disabled) { background: #344940; border-color: #8c9e92; }
.sc-editor .sc-actions .sc-primary { background: #eddaae; border-color: #eddaae; color: #263128; }
.sc-editor .sc-actions .sc-primary:hover:not(:disabled) { background: #f5e7c6; border-color: #f5e7c6; }
.sc-editor .sc-actions button:focus-visible { outline-color: #f3dcaa; }

.sc-editor .sc-workspace {
    display: grid;
    align-items: start;
    grid-template-columns: minmax(0, 1fr) minmax(17.75rem, 20.5rem);
    gap: clamp(.85rem, 1.8vw, 1.5rem);
    max-width: var(--content);
    margin: auto;
    padding: 1.3rem var(--gutter) 2rem;
}
.sc-editor .sc-canvas-panel, .sc-editor .sc-inspector {
    min-width: 0;
    background: var(--surface);
    border: 1px solid var(--edge);
    border-radius: .85rem;
    overflow: hidden;
    box-shadow: 0 .25rem 1rem rgb(23 35 29 / 4%);
}
.sc-editor .sc-sheet-info {
    padding: .8rem 1rem;
    min-height: 4rem;
    justify-content: space-between;
    flex-wrap: wrap;
}
.sc-editor .sc-sheet-title { display: grid; gap: .12rem; }
.sc-editor .sc-sheet-title > span, .sc-editor .sc-canvas-tip {
    color: var(--muted);
    font-size: .8rem;
}
.sc-editor .sc-palette-tag {
    display: inline-flex;
    gap: .5rem;
    align-items: center;
    text-align: left;
    color: var(--ink);
    background: #f7f7f2;
    min-height: 2.25rem;
    font-size: .8rem;
}
.sc-editor .sc-palette-dot { width: .64rem; height: .64rem; flex: 0 0 .64rem; border-radius: 50%; background: #a27d34; }
.sc-editor .sc-chevron { width: .37rem; height: .37rem; border-right: 1px solid; border-bottom: 1px solid; transform: rotate(45deg); margin: -.2rem .06rem 0 .18rem; }
.sc-editor .sc-sheet-stage {
    display: grid;
    place-items: center;
    padding: clamp(.7rem, 2vw, 1.9rem);
    min-width: 0;
    background-color: #e4e7df;
    background-image: radial-gradient(#27352b2c .55px, transparent .65px);
    background-size: 12px 12px;
    border-block: 1px solid #d8ddd2;
}
.sc-editor .sc-sheet {
    container-type: inline-size;
    position: relative;
    isolation: isolate;
    width: min(100%, clamp(30rem, calc((100svh - 14.5rem) * var(--paper-ratio, 1.414)), 65rem));
    background: white;
    box-shadow: 0 1.1rem 2.5rem rgb(22 35 27 / 15%), 0 1px 5px rgb(22 35 27 / 14%);
}
.sc-editor .sc-canvas-tip { padding: .65rem 1rem; }
.sc-editor .sc-bg, .sc-editor .sc-frame, .sc-editor .sc-nodes { position: absolute; inset: 0; width: 100%; height: 100%; }
.sc-editor .sc-bg { object-fit: fill; }
.sc-editor .sc-frame { pointer-events: none; }
.sc-editor .sc-frame-part { position: absolute; }

.sc-editor button, .sc-editor select, .sc-editor input, .sc-editor textarea { font: inherit; color: inherit; }
.sc-editor button, .sc-editor select, .sc-editor input:not([type=checkbox]), .sc-editor textarea {
    border: 1px solid var(--control-edge);
    border-radius: .46rem;
    background: var(--surface);
    padding: .48rem .65rem;
    min-height: 2.55rem;
}
.sc-editor :is(button, select, input[type=color], input[type=checkbox], label[for]) { cursor: pointer; }
.sc-editor button { font-weight: 600; transition: background-color 140ms ease, border-color 140ms ease; }
.sc-editor button:hover:not(:disabled) { background: #edf1ea; border-color: #97a79b; }
.sc-editor button:disabled { cursor: wait; opacity: .65; }
.sc-editor :is(button, input, select, textarea, a):focus-visible { outline: 2px solid var(--focus); outline-offset: 3px; }

.sc-editor .sc-node {
    all: unset;
    box-sizing: border-box;
    position: absolute;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    overflow: hidden;
    touch-action: none;
    user-select: none;
    cursor: grab;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    line-height: 1.15;
}
.sc-editor .sc-node:hover, .sc-editor .sc-node[aria-pressed=true], .sc-editor .sc-node:focus-visible {
    outline: 1px solid #34785fa8;
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
.sc-editor .sc-qr-grip { display: none; position: absolute; bottom: 0; right: 0; width: 1.1rem; height: 1.1rem; border-right: 3px solid var(--focus); border-bottom: 3px solid var(--focus); cursor: nwse-resize; }
.sc-editor .sc-node[aria-pressed=true] .sc-qr-grip { display: block; }

.sc-editor .sc-tabs { display: grid; grid-template-columns: 1fr 1fr; padding: .4rem; gap: .3rem; background: #f0f2ec; border-bottom: 1px solid var(--edge); }
.sc-editor .sc-tabs button { border-color: transparent; background: transparent; color: var(--muted); }
.sc-editor .sc-tabs button[aria-selected=true] { background: var(--surface); border-color: var(--edge); color: var(--ink); box-shadow: 0 1px 3px rgb(22 35 27 / 7%); }
.sc-editor .sc-theme-panel, .sc-editor .sc-properties { padding: 1rem; display: grid; gap: .85rem; }
.sc-editor .sc-group-heading { padding-top: .8rem; border-top: 1px solid var(--edge); }
.sc-editor .sc-palettes { display: grid; gap: .45rem; }
.sc-editor .sc-palette-btn { display: flex; align-items: center; gap: .6rem; text-align: left; min-height: 2.85rem; }
.sc-editor .sc-palette-btn[aria-pressed=true] { border-color: #7d9d8b; background: #f0f5ed; }
.sc-editor .sc-swatches { display: inline-flex; flex-shrink: 0; border-radius: .2rem; overflow: hidden; border: 1px solid var(--edge); }
.sc-editor .sc-swatches span { width: .62rem; height: 1.32rem; display: block; }
.sc-editor .sc-palette-name { font-size: .83rem; font-weight: 600; line-height: 1.25; }
.sc-editor .sc-theme-colors, .sc-editor .sc-image-controls, .sc-editor .sc-paper-options { display: grid; gap: .65rem; }
.sc-editor .sc-image-controls button, .sc-editor .sc-paper-options button { justify-self: start; }
.sc-editor label, .sc-editor .sc-color-field { display: grid; gap: .3rem; min-width: 0; align-content: start; }
.sc-editor label { font-size: .8rem; font-weight: 600; }
.sc-editor .sc-color-inputs { display: grid; grid-template-columns: 2.4rem minmax(0, 1fr); gap: .35rem; align-items: center; }
.sc-editor input[type=color] { padding: .17rem; width: 2.4rem; }
.sc-editor input[data-theme-hex], .sc-editor input[data-key-hex] { font-family: ui-monospace, monospace; font-size: .78rem; text-transform: lowercase; padding-inline: .45rem; }
.sc-editor input[aria-invalid=true] { border-color: var(--danger); background: #fff4f1; }
.sc-editor .sc-contrast-note { font-size: .8rem; color: var(--danger); padding: .45rem .6rem; border: 1px solid #edcbc6; border-radius: .4rem; background: #fff4f1; }
.sc-editor :is(select, textarea, input:not([type=checkbox]):not([type=color])) { width: 100%; min-width: 0; }
.sc-editor textarea { resize: vertical; }
.sc-editor .sc-paper-options small { font-weight: 400; color: var(--muted); }
.sc-editor .sc-check { display: flex; align-items: center; gap: .45rem; min-height: 2.15rem; cursor: pointer; }
.sc-editor .sc-check input { width: 1rem; height: 1rem; accent-color: var(--focus); }
.sc-editor .sc-pair, .sc-editor .sc-add { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: .6rem; }
.sc-editor .sc-add { grid-template-columns: minmax(0, 1fr) auto; align-items: end; }
.sc-editor .sc-delete { justify-self: start; color: var(--danger); border-color: #d9b4ad; }
.sc-editor .sc-delete:hover:not(:disabled) { background: #fff4f1; border-color: var(--danger); }
.sc-editor .sc-status { max-width: var(--content); margin: .6rem auto -.65rem; padding: 0 var(--gutter); color: var(--focus); font-size: .84rem; font-weight: 600; }
.sc-editor .sc-status[data-error] { color: var(--danger); }

.sc-editor .sc-preview { position: fixed; inset: 0; margin: auto; padding: 1rem; width: min(94vw, 72rem); max-width: 96vw; border: 1px solid var(--edge); border-radius: .8rem; background: var(--surface); color: var(--ink); }
.sc-editor .sc-preview::backdrop { background: rgb(16 32 25 / 68%); }
.sc-editor .sc-preview a { margin-left: auto; color: var(--ink); padding: .35rem .2rem; }
.sc-editor .sc-preview iframe { display: block; margin-top: .75rem; width: 100%; height: 74vh; border: 1px solid var(--edge); }

@media (max-width: 56rem) {
    .sc-editor .sc-workspace { grid-template-columns: 1fr; }
    .sc-editor .sc-sheet { width: min(100%, 44rem); }
}
@media (max-width: 34rem) {
    .sc-editor .sc-actions { width: 100%; }
    .sc-editor .sc-actions button { flex: 1; }
    .sc-editor .sc-head-inner { padding-block: .72rem; }
    .sc-editor .sc-workspace { padding-top: .8rem; }
    .sc-editor .sc-sheet-info { padding: .6rem .75rem; }
    .sc-editor .sc-canvas-tip { padding: .55rem .75rem; }
}
@media (prefers-reduced-motion: reduce) {
    .sc-editor button { transition: none; }
}
