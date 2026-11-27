body:has(> .sc-editor) { margin: 0; background: #f2f6fc; }

.sc-editor {
    --ink: #0f1f38;
    --muted: #526582;
    --surface: #ffffff;
    --edge: #dbe5f3;
    --control-edge: #c4d5eb;
    --focus: #0066ff;
    --focus-dark: #004ec2;
    --cyan: #00c4ff;
    --danger: #b42318;
    --gutter: clamp(.8rem, 2.4vw, 2rem);
    --content: 98rem;
    min-height: 100dvh;
    color: var(--ink);
    background: #f2f6fc;
    font: .9rem/1.45 system-ui, -apple-system, sans-serif;
}

.sc-editor *, .sc-editor *::before, .sc-editor *::after { box-sizing: border-box; }
.sc-editor [hidden] { display: none !important; }
.sc-editor .sc-work { min-width: 0; border: 0; padding: 0; margin: 0; }
.sc-editor h1, .sc-editor h2, .sc-editor p { margin: 0; }
.sc-editor h1 { font-size: clamp(.98rem, 1.7vw, 1.15rem); font-weight: 700; letter-spacing: -.01em; line-height: 1.2; }
.sc-editor h2 { font-size: .92rem; font-weight: 650; color: #0b254b; }

.sc-editor .sc-head {
    color: #f4f8ff;
    background: linear-gradient(135deg, #07142b 0%, #0d244d 58%, #0a3b7a 100%);
    border-bottom: 1px solid #091a38;
    box-shadow: 0 2px 12px rgb(7 20 43 / 14%);
}
.sc-editor .sc-head-inner,
.sc-editor .sc-title,
.sc-editor .sc-actions,
.sc-editor .sc-sheet-info,
.sc-editor .sc-sheet-meta,
.sc-editor .sc-preview > div { display: flex; align-items: center; gap: .55rem; }

.sc-editor .sc-head-inner {
    max-width: var(--content);
    margin: auto;
    padding: .45rem var(--gutter);
    min-height: 3.15rem;
    justify-content: space-between;
    gap: .6rem 1rem;
    flex-wrap: wrap;
}

.sc-editor .sc-title { min-width: 0; gap: .55rem; }
.sc-editor .sc-brand {
    color: #7cd4ff;
    font-weight: 700;
    font-size: .88rem;
    line-height: 1.15;
    padding-right: .7rem;
    border-right: 1px solid rgb(124 212 255 / 28%);
    margin-right: .1rem;
}
.sc-editor .sc-logo-badge {
    width: 1.78rem;
    height: 1.78rem;
    border-radius: .45rem;
    background: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .16rem;
    box-shadow: 0 2px 8px rgb(0 150 255 / 25%);
    flex-shrink: 0;
}
.sc-editor .sc-logo-img { width: 100%; height: 100%; object-fit: contain; display: block; }
.sc-editor .sc-actions { flex-wrap: wrap; }
.sc-editor .sc-actions button,
.sc-editor .sc-head-link {
    display: inline-flex;
    align-items: center;
    padding: .34rem .78rem;
    min-height: 2.05rem;
    font-size: .84rem;
    border: 1px solid rgb(255 255 255 / 24%);
    border-radius: .45rem;
    background: rgb(255 255 255 / 10%);
    color: #f4f8ff;
    font-weight: 600;
    text-decoration: none;
}
.sc-editor .sc-actions button:hover:not(:disabled),
.sc-editor .sc-head-link:hover { background: rgb(255 255 255 / 18%); border-color: rgb(255 255 255 / 42%); }
.sc-editor .sc-actions .sc-primary {
    background: linear-gradient(135deg, #00a3ff, #0052ff);
    border-color: #218bff;
    color: #ffffff;
    box-shadow: 0 2px 10px rgb(0 132 255 / 35%);
}
.sc-editor .sc-actions .sc-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #0092eb, #0046db);
    border-color: #4aa3ff;
}
.sc-editor .sc-actions button:focus-visible,
.sc-editor .sc-head-link:focus-visible { outline-color: #7cd4ff; }

.sc-editor .sc-workspace {
    display: grid;
    align-items: start;
    grid-template-columns: minmax(0, 1fr) minmax(18.5rem, 21rem);
    gap: clamp(.75rem, 1.4vw, 1.25rem);
    max-width: var(--content);
    margin: auto;
    padding: .7rem var(--gutter) 1.2rem;
}
.sc-editor .sc-canvas-panel, .sc-editor .sc-inspector {
    min-width: 0;
    background: var(--surface);
    border: 1px solid var(--edge);
    border-radius: .8rem;
    overflow: hidden;
    box-shadow: 0 .35rem 1.25rem rgb(15 31 56 / 5%);
}
.sc-editor .sc-sheet-info {
    padding: .5rem .9rem;
    min-height: 2.9rem;
    justify-content: space-between;
    flex-wrap: wrap;
}
.sc-editor .sc-sheet-title { display: flex; align-items: baseline; gap: .5rem; }
.sc-editor .sc-sheet-title > span, .sc-editor .sc-canvas-tip {
    color: var(--muted);
    font-size: .78rem;
}
.sc-editor .sc-palette-tag {
    display: inline-flex;
    gap: .45rem;
    align-items: center;
    text-align: left;
    color: var(--ink);
    background: #f4f8ff;
    border-color: #c8daf5;
    min-height: 1.95rem;
    padding: .25rem .6rem;
    font-size: .78rem;
}
.sc-editor .sc-palette-dot { width: .6rem; height: .6rem; flex: 0 0 .6rem; border-radius: 50%; background: #0066ff; }
.sc-editor .sc-chevron { width: .35rem; height: .35rem; border-right: 1px solid; border-bottom: 1px solid; transform: rotate(45deg); margin: -.18rem .05rem 0 .14rem; }
.sc-editor .sc-sheet-stage {
    display: grid;
    place-items: center;
    padding: clamp(.55rem, 1.2vw, 1.1rem);
    min-width: 0;
    background-color: #e4ecf8;
    background-image: radial-gradient(#0b3a7830 .65px, transparent .7px);
    background-size: 14px 14px;
    border-block: 1px solid #d5e1f2;
}
.sc-editor .sc-sheet {
    position: relative;
    isolation: isolate;
    width: min(100%, clamp(24rem, calc((100svh - 10.5rem) * var(--paper-ratio, 1.414)), 62rem));
    background: white;
    box-shadow: 0 1.1rem 2.5rem rgb(11 36 77 / 16%), 0 1px 5px rgb(11 36 77 / 12%);
}
.sc-editor .sc-canvas-tip { padding: .42rem .9rem; }
.sc-editor .sc-bg, .sc-editor .sc-frame, .sc-editor .sc-nodes { position: absolute; inset: 0; width: 100%; height: 100%; }
.sc-editor .sc-nodes { container-type: inline-size; }
.sc-editor .sc-bg { object-fit: fill; }
.sc-editor .sc-frame { pointer-events: none; }
.sc-editor .sc-frame-part { position: absolute; }

.sc-editor button, .sc-editor select, .sc-editor input, .sc-editor textarea { font: inherit; color: inherit; }
.sc-editor button, .sc-editor select, .sc-editor input:not([type=checkbox]), .sc-editor textarea {
    border: 1px solid var(--control-edge);
    border-radius: .48rem;
    background: var(--surface);
    padding: .48rem .65rem;
    min-height: 2.55rem;
}
.sc-editor :is(button, select, input[type=color], input[type=checkbox], label[for]) { cursor: pointer; }
.sc-editor button { font-weight: 600; transition: background-color 140ms ease, border-color 140ms ease; }
.sc-editor button:hover:not(:disabled) { background: #edf3fc; border-color: #93b3e3; }
.sc-editor button:disabled { cursor: wait; opacity: .65; }
.sc-editor :is(button, input, select, textarea, a):focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; }

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
.sc-editor .sc-node-content {
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none;
}
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
    width: .62rem;
    height: .62rem;
    background: #ffffff;
    border: 2px solid var(--focus);
    border-radius: 2px;
    z-index: 5;
    box-shadow: 0 1px 3px rgb(0 56 168 / 28%);
}
.sc-editor .sc-node[aria-pressed=true] .sc-handle { display: block; }
.sc-editor .sc-handle[data-handle=nw] { top: -.32rem; left: -.32rem; cursor: nwse-resize; }
.sc-editor .sc-handle[data-handle=ne] { top: -.32rem; right: -.32rem; cursor: nesw-resize; }
.sc-editor .sc-handle[data-handle=sw] { bottom: -.32rem; left: -.32rem; cursor: nesw-resize; }
.sc-editor .sc-handle[data-handle=se] { bottom: -.32rem; right: -.32rem; cursor: nwse-resize; }
.sc-editor .sc-handle[data-handle=n]  { top: -.32rem; left: calc(50% - .31rem); cursor: ns-resize; }
.sc-editor .sc-handle[data-handle=s]  { bottom: -.32rem; left: calc(50% - .31rem); cursor: ns-resize; }
.sc-editor .sc-handle[data-handle=w]  { left: -.32rem; top: calc(50% - .31rem); cursor: ew-resize; }
.sc-editor .sc-handle[data-handle=e]  { right: -.32rem; top: calc(50% - .31rem); cursor: ew-resize; }

.sc-editor .sc-tabs { display: grid; grid-template-columns: 1fr 1fr; padding: .4rem; gap: .3rem; background: #eef4fc; border-bottom: 1px solid var(--edge); }
.sc-editor .sc-tabs.sc-tabs-3 { grid-template-columns: repeat(3, 1fr); }
.sc-editor .sc-tabs button { border-color: transparent; background: transparent; color: var(--muted); padding: .42rem .35rem; font-size: .82rem; }
.sc-editor .sc-tabs button[aria-selected=true] { background: var(--surface); border-color: #c7daf5; color: var(--focus-dark); box-shadow: 0 1px 4px rgb(0 82 224 / 10%); }
.sc-editor .sc-theme-panel, .sc-editor .sc-properties { padding: 1rem; display: grid; gap: .85rem; }
.sc-editor .sc-group-heading { padding-top: .8rem; border-top: 1px solid var(--edge); }
.sc-editor .sc-palettes { display: grid; gap: .45rem; }
.sc-editor .sc-palette-btn { display: flex; align-items: center; gap: .6rem; text-align: left; min-height: 2.85rem; }
.sc-editor .sc-palette-btn[aria-pressed=true] { border-color: #3b82f6; background: #eff6ff; }
.sc-editor .sc-swatches { display: inline-flex; flex-shrink: 0; border-radius: .2rem; overflow: hidden; border: 1px solid var(--edge); }
.sc-editor .sc-swatches span { width: .62rem; height: 1.32rem; display: block; }
.sc-editor .sc-palette-name { font-size: .83rem; font-weight: 600; line-height: 1.25; }
.sc-editor .sc-theme-colors, .sc-editor .sc-image-controls, .sc-editor .sc-paper-options, .sc-editor .sc-custom-var-form, .sc-editor .sc-custom-var-list { display: grid; gap: .6rem; }
.sc-editor .sc-image-controls button, .sc-editor .sc-paper-options button { justify-self: start; }
.sc-editor label, .sc-editor .sc-color-field { display: grid; gap: .3rem; min-width: 0; align-content: start; }
.sc-editor label { font-size: .8rem; font-weight: 600; color: #1d3557; }
.sc-editor label small { font-weight: 400; color: var(--muted); }
.sc-editor .sc-color-inputs { display: grid; grid-template-columns: 2.4rem minmax(0, 1fr); gap: .35rem; align-items: center; }
.sc-editor input[type=color] { padding: .17rem; width: 2.4rem; }
.sc-editor input[data-theme-hex], .sc-editor input[data-key-hex] { font-family: ui-monospace, monospace; font-size: .78rem; text-transform: lowercase; padding-inline: .45rem; }
.sc-editor input[aria-invalid=true] { border-color: var(--danger); background: #fff4f1; }
.sc-editor .sc-contrast-note { font-size: .8rem; color: var(--danger); padding: .45rem .6rem; border: 1px solid #f3c4be; border-radius: .4rem; background: #fff4f2; }
.sc-editor :is(select, textarea, input:not([type=checkbox]):not([type=color])) { width: 100%; min-width: 0; background: #fbfdff; }
.sc-editor textarea { resize: vertical; }
.sc-editor .sc-paper-options small { font-weight: 400; color: var(--muted); }
.sc-editor .sc-check { display: flex; align-items: center; gap: .45rem; min-height: 2.15rem; cursor: pointer; }
.sc-editor .sc-check input { width: 1rem; height: 1rem; accent-color: var(--focus); }
.sc-editor .sc-pair, .sc-editor .sc-add { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: .6rem; }
.sc-editor .sc-add { grid-template-columns: minmax(0, 1fr) auto; align-items: end; }
.sc-editor [data-action=add],
.sc-editor [data-action=add-custom-var] { background: #e8f2ff; border-color: #9ec5fe; color: #0048b8; }
.sc-editor [data-action=add]:hover:not(:disabled),
.sc-editor [data-action=add-custom-var]:hover:not(:disabled) { background: #d6e8ff; border-color: #6ea8fe; }
.sc-editor .sc-delete { justify-self: start; color: var(--danger); border-color: #f3c4be; }
.sc-editor .sc-delete:hover:not(:disabled) { background: #fff4f2; border-color: var(--danger); }
.sc-editor .sc-status { max-width: var(--content); min-height: 1.15rem; margin: .3rem auto -.45rem; padding: 0 var(--gutter); color: var(--focus-dark); font-size: .8rem; font-weight: 600; }
.sc-editor .sc-status[hidden] { display: block !important; visibility: hidden; }
.sc-editor .sc-status[data-error] { color: var(--danger); }
.sc-editor .sc-token-help { font-size: .76rem; color: var(--muted); line-height: 1.45; }
.sc-editor .sc-token-help code { background: #eef4fc; color: #0b3a78; padding: .08rem .3rem; border-radius: .2rem; font-size: .74rem; }
.sc-editor .sc-cv-item { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .45rem .6rem; border: 1px solid var(--edge); border-radius: .45rem; background: #f5f9ff; font-size: .8rem; }
.sc-editor .sc-cv-item button { min-height: 1.8rem; padding: .2rem .5rem; font-size: .75rem; color: var(--danger); border-color: #f3c4be; }
.sc-editor .sc-preset-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
.sc-editor .sc-preset-card { display: grid; gap: .3rem; padding: .35rem; border: 1px solid var(--edge); border-radius: .5rem; background: #fbfdff; text-align: left; min-height: 0; }
.sc-editor .sc-preset-card img { width: 100%; aspect-ratio: 16 / 10; object-fit: cover; border-radius: .3rem; border: 1px solid #e2eaf5; display: block; }
.sc-editor .sc-preset-card span { font-size: .74rem; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sc-editor .sc-preset-card[aria-pressed=true] { border-color: var(--focus); background: #eff6ff; box-shadow: 0 0 0 1px var(--focus); }

.sc-editor .sc-preview { position: fixed; inset: 0; margin: auto; padding: 1rem; width: min(94vw, 72rem); max-width: 96vw; border: 1px solid var(--edge); border-radius: .85rem; background: var(--surface); color: var(--ink); }
.sc-editor .sc-preview::backdrop { background: rgb(7 20 43 / 72%); }
.sc-editor .sc-preview a { margin-left: auto; color: var(--focus-dark); font-weight: 600; padding: .35rem .2rem; }
.sc-editor .sc-preview iframe { display: block; margin-top: .75rem; width: 100%; height: 74vh; border: 1px solid var(--edge); border-radius: .45rem; }

@media (max-width: 56rem) {
    .sc-editor .sc-workspace { grid-template-columns: 1fr; }
    .sc-editor .sc-sheet { width: min(100%, 44rem); }
}
@media (max-width: 34rem) {
    .sc-editor .sc-actions { width: 100%; }
    .sc-editor .sc-actions button, .sc-editor .sc-head-link { flex: 1; justify-content: center; }
    .sc-editor .sc-head-inner { padding-block: .72rem; }
    .sc-editor .sc-workspace { padding-top: .8rem; }
    .sc-editor .sc-sheet-info { padding: .6rem .75rem; }
    .sc-editor .sc-canvas-tip { padding: .55rem .75rem; }
}
@media (prefers-reduced-motion: reduce) {
    .sc-editor button { transition: none; }
}
