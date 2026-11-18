.sc-editor { --ink: #213e35; --edge: #b5c1b8; font: 1rem/1.45 system-ui, sans-serif; color: var(--ink); background: #f3f4ef; padding: clamp(.8rem, 2.5vw, 2.5rem); min-height: 96vh; }
.sc-editor *, .sc-editor *::before { box-sizing: border-box; }
.sc-editor [hidden] { display: none !important; }
.sc-editor .sc-work { min-width: 0; border: 0; padding: 0; margin: 0 auto; max-width: 95rem; }
.sc-editor h1, .sc-editor h2, .sc-editor p { margin: 0; }
.sc-editor h1 { font: normal clamp(2rem, 4vw, 3.1rem)/1.15 Georgia, serif; }
.sc-editor h2 { font-size: 1.2rem; font-weight: 650; }
.sc-editor .sc-head, .sc-editor .sc-actions, .sc-editor .sc-sheet-info, .sc-editor .sc-paper-options, .sc-editor .sc-preview > div { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
.sc-editor .sc-head, .sc-editor .sc-sheet-info { justify-content: space-between; }
.sc-editor .sc-head { border-bottom: 2px solid var(--ink); padding: .55rem 0 1.15rem; margin-bottom: 1.25rem; }
.sc-editor .sc-workspace { display: grid; align-items: start; grid-template-columns: minmax(0, 1fr) minmax(15rem, 19rem); gap: clamp(1rem, 2vw, 2rem); }
.sc-editor .sc-canvas-panel, .sc-editor .sc-properties { min-width: 0; }
.sc-editor .sc-sheet-info { margin-bottom: .65rem; font-size: .9rem; }
.sc-editor .sc-sheet { container-type: inline-size; position: relative; isolation: isolate; width: 100%; background: white; outline: 1px solid var(--edge); }
.sc-editor .sc-bg, .sc-editor .sc-nodes { position: absolute; inset: 0; width: 100%; height: 100%; }
.sc-editor .sc-bg { object-fit: fill; }
.sc-editor .sc-frame { position: absolute; border: 1px solid #335148; }
.sc-editor button, .sc-editor select, .sc-editor input, .sc-editor textarea { font: inherit; color: inherit; }
.sc-editor button, .sc-editor select, .sc-editor input:not([type=checkbox]), .sc-editor textarea { border: 1px solid var(--edge); border-radius: .25rem; background: white; padding: .45rem .65rem; min-height: 2.65rem; }
.sc-editor button { cursor: pointer; }
.sc-editor button:disabled { cursor: wait; }
.sc-editor .sc-primary { background: #285541; border-color: #285541; color: white; }
.sc-editor :is(button,input,select,textarea,a):focus-visible { outline: 3px solid #a23e12; outline-offset: 2px; }
.sc-editor .sc-node { all: unset; box-sizing: border-box; position: absolute; display: flex; flex-direction: column; justify-content: flex-start; overflow: hidden; touch-action: none; user-select: none; cursor: grab; white-space: pre-wrap; overflow-wrap: anywhere; line-height: 1.2; }
.sc-editor .sc-node:hover, .sc-editor .sc-node[aria-pressed=true], .sc-editor .sc-node:focus-visible { outline: 2px solid #b65122; }
.sc-editor .sc-node:active { cursor: grabbing; }
.sc-editor .sc-node img { display: block; width: 100%; height: 100%; pointer-events: none; }
.sc-editor .sc-node[data-qr-node] { z-index: 1; }
.sc-editor .sc-qr-grip { display: none; position: absolute; bottom: 0; right: 0; width: 1.1rem; height: 1.1rem; border-right: 4px solid #934516; border-bottom: 4px solid #934516; cursor: nwse-resize; }
.sc-editor .sc-node[aria-pressed=true] .sc-qr-grip { display: block; }
.sc-editor label { display: grid; gap: .25rem; min-width: 0; font-size: .88rem; font-weight: 550; }
.sc-editor :is(select,textarea,input:not([type=checkbox])) { width: 100%; min-width: 0; }
.sc-editor input[type=color] { cursor: pointer; }
.sc-editor .sc-paper-options { margin: 1rem 0; align-items: end; gap: 1rem; }
.sc-editor .sc-paper-options small { font-weight: normal; }
.sc-editor .sc-paper-options label:first-child { display: block; }
.sc-editor .sc-paper-options input[type=file] { display: block; max-width: 18rem; margin-top: .25rem; }
.sc-editor .sc-check { display: flex; align-items: center; min-height: 2.65rem; cursor: pointer; }
.sc-editor .sc-properties { background: white; border: 1px solid var(--edge); padding: 1rem; display: grid; gap: .8rem; }
.sc-editor .sc-pair, .sc-editor .sc-add { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
.sc-editor .sc-add { grid-template-columns: 1fr auto; align-items: end; }
.sc-editor hr { width: 100%; margin: .2rem 0; border: 0; border-top: 1px solid var(--edge); }
.sc-editor .sc-delete { justify-self: start; color: #873c30; }
.sc-editor .sc-status { max-width: 95rem; margin: 0 auto .6rem; font-weight: 550; }
.sc-editor .sc-status[data-error] { color: #892e1f; }
.sc-editor .sc-preview { position: fixed; inset: 0; margin: auto; padding: 1rem; width: min(92vw, 70rem); max-width: 96vw; border: 1px solid var(--edge); background: white; color: var(--ink); }
.sc-editor .sc-preview::backdrop { background: rgb(15 32 24 / 55%); }
.sc-editor .sc-preview a { margin-left: auto; color: #285541; text-decoration: underline; }
.sc-editor .sc-preview iframe { display: block; margin-top: .75rem; width: 100%; height: 70vh; border: 0; }
@media (max-width: 55rem) {
    .sc-editor .sc-workspace { grid-template-columns: 1fr; }
    .sc-editor .sc-properties { grid-template-columns: repeat(auto-fit, minmax(min(100%, 15rem), 1fr)); align-items: end; }
    .sc-editor .sc-properties h2, .sc-editor hr { grid-column: 1 / -1; }
}
