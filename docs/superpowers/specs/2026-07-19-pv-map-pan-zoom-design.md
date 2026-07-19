# PV Module Map — Pan & Zoom

## Problem

The "Peta Susunan PV Module" grid on the dashboard (`resources/views/dashboard.blade.php`) renders each transformer block's module layout as an HTML `<table>` inside a horizontally-scrolling container (`overflow-x-auto`). For large blocks the grid is wider than the viewport, and plain horizontal scroll cuts content off on the left/right with no way to see the whole layout at once or zoom out for an overview.

## Goal

Let the user freely pan (drag) and zoom the module grid within its card, without breaking the two existing interactive modes already built into that view:
- **Atur Posisi** (native HTML5 drag-and-drop to reposition a module in the grid)
- **Edit Data** (click a cell to assign/replace/remove an asset) and normal click-through to an asset's detail page

## Non-Goals

- No changes to any PHP controller, route, model, or database — this is a client-side (Blade view + vanilla JS) change only.
- No changes to the PV map editing/data-assignment logic itself.
- No persistence of pan/zoom position across a full page reload (in-memory only, per block, for the lifetime of the page view).
- No changes to `resources/views/components/pv-map-modal.blade.php` (a separate component, not in scope — the user specifically asked about the dashboard page's map).

## Design

### Structure

Each block's existing markup:

```
<div class="px-6 pb-6 overflow-x-auto">          <!-- becomes the viewport -->
    ...
    <div class="pt-1 flex justify-center">        <!-- becomes the canvas -->
        <table>...</table>
    </div>
    ...
</div>
```

becomes a **viewport** (fixed height, `overflow:hidden`, `position:relative`, cursor `grab`/`grabbing`) wrapping a **canvas** div that receives `style="transform: translate(Xpx, Ypx) scale(S)"`. The table itself is untouched — all existing `data-row`/`data-col`/`data-asset-id` attributes, the drag-and-drop reordering logic, and the click handlers keep working exactly as they do today, because none of that code depends on the table's on-screen position.

Small floating **+ / − / Reset** buttons sit in the top-right corner of the viewport (`position:absolute`), always visible, for touch/no-wheel users and precise control.

### State

Per-block view state lives in the existing Alpine `x-data` on `#peta-pv` (the same root that already tracks `activeBlock`, `blocks`, etc.):

```js
mapView: {},  // { [blockName]: { x: 0, y: 0, scale: 1 } }
getView(block) { return this.mapView[block] ??= { x: 0, y: 0, scale: 1 }; }
```

Switching blocks (prev/next/dropdown) does not reset this — each block remembers its own last pan/zoom for the rest of the page's lifetime (confirmed with user). A page reload resets everything to `{x:0, y:0, scale:1}` since nothing is persisted server-side or in `localStorage`.

### Interactions

**Zoom**
- Mouse wheel over the viewport: `preventDefault()` (stop page scroll), zoom in/out by a fixed factor per notch (~10%), anchored at the cursor position (the point under the cursor stays visually fixed).
- `+` / `−` buttons: same factor, anchored at the viewport's center.
- `Reset` button: sets that block's view back to `{x:0, y:0, scale:1}`.
- Clamped to **30%–300%**.

**Pan (mouse)**
- `mousedown` on the canvas: if the target is inside a `.pv-asset` element (a module/supporting-asset cell), do nothing here — let the existing native `draggable` handling (Atur Posisi) or the plain link click (normal mode) proceed untouched.
- Otherwise (background: empty dashed placeholder cells, table padding, divider gaps): track the drag. Require the pointer to move past a small threshold (~4px) before treating it as a pan — this keeps a plain click on an empty cell (which opens the "add asset" modal in Edit Data mode) working, since a true click never crosses the threshold.
- Once past the threshold, `translate` updates live with the pointer; on release, the final `x`/`y` are kept in that block's `mapView` entry.

**Pan/zoom (touch)**
- One-finger drag on background (same target check as mouse) pans.
- Two-finger touch: pinch distance change drives zoom, anchored at the midpoint between the two touches; a small initial-move threshold is not needed here since two-finger touch is unambiguous (it can't be a tap-through to a link).

**Compatibility**
- Atur Posisi drag-to-reposition and the Edit Data modal continue to work exactly as before — the pan/zoom layer only ever engages when the interaction starts on the grid's empty background, never on a `.pv-asset` element.
- Normal-mode click-through to `assets.show` is unaffected (link clicks with no mouse movement are never intercepted).

### Implementation

All new code lives in `resources/views/dashboard.blade.php`:
- Wrapper `div` markup changes (viewport/canvas split) inside the existing `@foreach($pvMapData as $block => $modules)` loop.
- A block of new vanilla JS in the existing `@push('scripts')` section (same file/pattern as the existing PV drag-and-drop code — no new dependency, consistent with the codebase's existing hand-rolled approach for this exact table).
- A few Alpine bindings on the per-block canvas div (`:style`, event handlers already delegate through plain `addEventListener` the same way the existing drag-and-drop code does, for consistency).

### Testing

Pure front-end interaction — no PHP unit/feature tests apply (no backend/data change). Verification is manual, in a running browser: pan via mouse drag on background, zoom via wheel and buttons, confirm Atur Posisi module dragging still works, confirm normal-mode click still navigates to asset detail, confirm Edit Data cell-click still opens the modal, confirm switching blocks preserves each block's own pan/zoom state.

## Self-Review

- **Placeholder scan:** none.
- **Consistency:** interaction rules match what was confirmed with the user (background-only pan, wheel + buttons zoom, per-block memory, touch support, 30–300% clamp).
- **Scope:** single view file, no backend — appropriately small for one implementation pass.
