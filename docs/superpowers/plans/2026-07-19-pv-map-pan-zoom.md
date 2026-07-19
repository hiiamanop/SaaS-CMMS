# PV Module Map Pan & Zoom Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let the "Peta Susunan PV Module" grid on the dashboard be freely panned (dragged) and zoomed in/out, without breaking the existing Atur Posisi (drag-to-reposition) and Edit Data (click-to-assign) modes or normal click-through to an asset's detail page.

**Architecture:** Wrap each block's existing `<table>` in a fixed-height `overflow:hidden` viewport `div` and a `transform`-driven canvas `div`. All new interaction logic is plain vanilla JS (global event delegation via `document.addEventListener`, directly writing `element.style.transform`), matching the file's existing hand-rolled pattern for PV drag-and-drop and the tooltip — no new dependency, no Alpine reactive state for the high-frequency mouse/touch-move updates.

**Tech Stack:** Laravel Blade, Alpine.js (existing `@click` bindings only, not used for the pan/zoom engine itself), vanilla JS, Tailwind CSS. No new packages.

## Global Constraints

- Zoom range: **30%–300%** (`PV_MIN_SCALE = 0.3`, `PV_MAX_SCALE = 3`).
- Zoom step per wheel notch / button click: **15%** (`PV_ZOOM_STEP = 1.15`).
- Pan drag-start threshold: **4px** (`PV_DRAG_THRESHOLD = 4`) — pointer must move at least this far before a mousedown/touchstart is treated as a pan, so a plain click/tap still reaches its original handler (module link navigation, Edit Data cell click).
- Pan only engages when the interaction starts on the grid's **background** (not on a `.pv-asset` element) — this preserves the existing native HTML5 drag-and-drop reordering (Atur Posisi) and normal click-through untouched.
- Viewport fixed height: **480px** per block.
- No backend/PHP/route/migration changes — this is `resources/views/dashboard.blade.php` only.
- No persistence of pan/zoom across a full page reload — in-memory per block (`window.pvMapView[block]`) for the life of the page view, reset to `{x:0, y:0, scale:1}` on reload.
- Every task is manually verified in a real browser (no PHPUnit applies — pure front-end interaction). Use the dev server and the Playwright MCP browser tools, logged in as `admin@cmms.com` / `password` (seeded in `database/seeders/UserSeeder.php`), at `/dashboard`.

---

## File Structure

Single file touched across all 4 tasks: `resources/views/dashboard.blade.php`.

- **Task 1** changes the per-block HTML structure (viewport/canvas wrapper + zoom buttons) and adds the core JS engine (state, transform application, button-driven zoom).
- **Task 2** adds mouse-wheel zoom (reuses Task 1's engine).
- **Task 3** adds mouse drag-to-pan + click-suppression-after-drag.
- **Task 4** adds touch (one-finger pan, two-finger pinch-zoom).

Tasks are **sequential, not parallelizable** — each edits the same `<script>` block built up by the previous task.

---

### Task 1: Viewport/canvas structure + button zoom

**Files:**
- Modify: `resources/views/dashboard.blade.php`

**Interfaces:**
- Produces (global JS, available to later tasks):
  - `window.pvMapView` — `{ [blockName]: { x: number, y: number, scale: number } }`
  - `PV_MIN_SCALE = 0.3`, `PV_MAX_SCALE = 3`, `PV_ZOOM_STEP = 1.15` (constants)
  - `pvGetView(block: string): { x, y, scale }` — lazily creates and returns the block's view state
  - `pvClampScale(scale: number): number`
  - `pvApplyTransform(block: string): void` — writes `translate(x,y) scale(s)` onto that block's `[data-pv-canvas]` element
  - `pvZoomAt(block: string, factor: number, cx: number, cy: number): void` — zoom by `factor`, anchored at viewport-relative point `(cx, cy)`
  - `pvZoomBy(block: string, direction: 'in' | 'out'): void` — zoom anchored at the viewport's own center
  - `pvResetView(block: string): void`
  - DOM markers: `[data-pv-viewport="{block}"]` (the clipping container), `[data-pv-canvas="{block}"]` (the transformed element)

- [ ] **Step 1: Replace the per-block container's class**

Find (appears once per block, inside the `@foreach($pvMapData as $block => $modules)` loop):

```blade
        <div x-show="activeBlock === '{{ $block }}'" x-cloak
             x-data="{ editMode: false }"
             class="px-6 pb-6 overflow-x-auto">
```

Replace with (drop `overflow-x-auto` — the new inner viewport now owns clipping):

```blade
        <div x-show="activeBlock === '{{ $block }}'" x-cloak
             x-data="{ editMode: false }"
             class="px-6 pb-6">
```

- [ ] **Step 2: Wrap the table in a viewport/canvas structure with zoom buttons**

Find:

```blade
            <div class="pt-1 flex justify-center">
                <table class="border-separate" style="border-spacing:3px;" id="pvGrid_{{ $block }}">
```

Replace with:

```blade
            <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-gray-50/50 mt-1 cursor-grab"
                 style="height: 480px;"
                 data-pv-viewport="{{ $block }}">

                <div class="absolute top-3 right-3 z-20 flex flex-col gap-1">
                    <button type="button" onclick="pvZoomBy('{{ $block }}', 'in')"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white shadow-md border border-gray-200 text-gray-600 hover:bg-gray-50 font-bold text-base leading-none">+</button>
                    <button type="button" onclick="pvZoomBy('{{ $block }}', 'out')"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white shadow-md border border-gray-200 text-gray-600 hover:bg-gray-50 font-bold text-base leading-none">&minus;</button>
                    <button type="button" onclick="pvResetView('{{ $block }}')"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white shadow-md border border-gray-200 text-gray-500 hover:bg-gray-50 text-[9px] font-bold">RST</button>
                </div>

                <div class="flex justify-center" data-pv-canvas="{{ $block }}"
                     style="width: max-content; padding-top: 4px; transform-origin: 0 0;">
                    <table class="border-separate" style="border-spacing:3px;" id="pvGrid_{{ $block }}">
```

- [ ] **Step 3: Close the two new wrapper divs**

Find (the matching close of the `pt-1 flex justify-center` div from Step 2 — the table's closing tags followed by that one closing `</div>`):

```blade
                    </tbody>
                </table>
            </div>

            {{-- Summary strip --}}
```

Replace with (one extra closing `</div>` for the new viewport wrapper):

```blade
                    </tbody>
                </table>
                </div>
            </div>

            {{-- Summary strip --}}
```

- [ ] **Step 4: Add the core JS engine**

Find the end of the existing `<script>` block:

```blade
        window.location.href = window.location.pathname + '#peta-pv';
        window.location.reload();
    }
</script>
@endpush
```

Replace with (adds the new engine after the existing `pvSave` function, before `</script>`):

```blade
        window.location.href = window.location.pathname + '#peta-pv';
        window.location.reload();
    }

    // PV Pan & Zoom
    window.pvMapView = {}; // { [block]: { x, y, scale } }
    const PV_MIN_SCALE = 0.3;
    const PV_MAX_SCALE = 3;
    const PV_ZOOM_STEP = 1.15;

    function pvGetView(block) {
        if (!window.pvMapView[block]) window.pvMapView[block] = { x: 0, y: 0, scale: 1 };
        return window.pvMapView[block];
    }

    function pvClampScale(scale) {
        return Math.min(PV_MAX_SCALE, Math.max(PV_MIN_SCALE, scale));
    }

    function pvApplyTransform(block) {
        const canvas = document.querySelector(`[data-pv-canvas="${block}"]`);
        if (!canvas) return;
        const v = pvGetView(block);
        canvas.style.transform = `translate(${v.x}px, ${v.y}px) scale(${v.scale})`;
    }

    // Zoom so the viewport-relative point (cx, cy) stays visually fixed.
    function pvZoomAt(block, factor, cx, cy) {
        const v = pvGetView(block);
        const newScale = pvClampScale(v.scale * factor);
        const appliedFactor = newScale / v.scale;
        v.x = cx - (cx - v.x) * appliedFactor;
        v.y = cy - (cy - v.y) * appliedFactor;
        v.scale = newScale;
        pvApplyTransform(block);
    }

    function pvZoomBy(block, direction) {
        const viewport = document.querySelector(`[data-pv-viewport="${block}"]`);
        if (!viewport) return;
        const rect = viewport.getBoundingClientRect();
        const factor = direction === 'in' ? PV_ZOOM_STEP : 1 / PV_ZOOM_STEP;
        pvZoomAt(block, factor, rect.width / 2, rect.height / 2);
    }

    function pvResetView(block) {
        window.pvMapView[block] = { x: 0, y: 0, scale: 1 };
        pvApplyTransform(block);
    }

    // Apply the identity transform to every rendered block's canvas on load.
    document.querySelectorAll('[data-pv-canvas]').forEach(el => {
        pvApplyTransform(el.dataset.pvCanvas);
    });
</script>
@endpush
```

- [ ] **Step 5: Verify with `php artisan view:cache` (catches Blade syntax errors)**

Run:

```bash
php artisan view:cache
```

Expected: `INFO  Blade templates cached successfully.` with no errors. Then run `php artisan view:clear` afterward so subsequent manual testing doesn't serve a stale cached view while you keep editing in later tasks.

```bash
php artisan view:clear
```

- [ ] **Step 6: Manual browser verification**

Start the dev server if it isn't already running:

```bash
php artisan serve --port=8099 > /tmp/pv-dashboard-serve.log 2>&1 &
```

(If port 8099 is already in use by a previous task's server, that's fine — reuse it, don't start a second instance. Check first with `lsof -i :8099` if unsure.)

Using the Playwright MCP browser tools:
1. Navigate to `http://127.0.0.1:8099/login`.
2. Log in as `admin@cmms.com` / `password`.
3. Navigate to `http://127.0.0.1:8099/dashboard`.
4. Take a snapshot and confirm the "Peta Susunan PV Module" card renders with the grid visible inside a bounded box (no more full-page horizontal overflow) and the +/−/RST buttons visible in its top-right corner.
5. Click the `+` button 3 times. Then use `browser_evaluate` to read the active block's canvas transform, e.g.:
   ```js
   () => {
     const el = document.querySelector('[data-pv-canvas]:not([style*="display: none"])') || document.querySelector('[data-pv-canvas]');
     return el.style.transform;
   }
   ```
   Expected: `scale(...)` reflects `1.15^3 ≈ 1.52` (approximately — clamped at 3 max, so if it's already near the max this confirms clamping instead; either outcome is correct as long as scale increased and never exceeds 3).
6. Click `RST`. Expected: transform resets to `translate(0px, 0px) scale(1)`.
7. Click the `−` button once. Expected: scale ≈ `1/1.15 ≈ 0.87`, never below 0.3.
8. Confirm normal-mode click on a PV module link still navigates to the asset's detail page (click one, expect URL to change to `/assets/{id}`).
9. Confirm `editDataMode` (the "✎ Edit Data" button) still opens the assign-asset modal when clicking an empty dashed cell.
10. Confirm "✎ Atur Posisi" still lets you drag a module `.pv-asset` element onto another cell (visual swap) — this task didn't touch that code path, but the DOM restructuring must not have broken it.

- [ ] **Step 7: Commit**

```bash
git add resources/views/dashboard.blade.php
git commit -m "feat: pannable/zoomable viewport for PV module map (button zoom)"
```

---

### Task 2: Mouse-wheel zoom

**Files:**
- Modify: `resources/views/dashboard.blade.php`

**Interfaces:**
- Consumes: `pvZoomAt(block, factor, cx, cy)`, `PV_ZOOM_STEP` (both from Task 1).
- Produces: a `wheel` event listener; no new named functions.

- [ ] **Step 1: Add the wheel listener**

Find (the end of the script, right after Task 1's `pvApplyTransform` initial-load loop, before the closing `</script>`):

```blade
    // Apply the identity transform to every rendered block's canvas on load.
    document.querySelectorAll('[data-pv-canvas]').forEach(el => {
        pvApplyTransform(el.dataset.pvCanvas);
    });
</script>
@endpush
```

Replace with:

```blade
    // Apply the identity transform to every rendered block's canvas on load.
    document.querySelectorAll('[data-pv-canvas]').forEach(el => {
        pvApplyTransform(el.dataset.pvCanvas);
    });

    // Wheel zoom, anchored at the cursor.
    document.addEventListener('wheel', e => {
        const viewport = e.target.closest('[data-pv-viewport]');
        if (!viewport) return;
        e.preventDefault();
        const block = viewport.dataset.pvViewport;
        const rect = viewport.getBoundingClientRect();
        const cx = e.clientX - rect.left;
        const cy = e.clientY - rect.top;
        const factor = e.deltaY < 0 ? PV_ZOOM_STEP : 1 / PV_ZOOM_STEP;
        pvZoomAt(block, factor, cx, cy);
    }, { passive: false });
</script>
@endpush
```

(`{ passive: false }` is required — without it, `e.preventDefault()` on a `wheel` listener is silently ignored by the browser and the page would scroll instead of the map zooming.)

- [ ] **Step 2: Manual browser verification**

Reuse the running dev server and the logged-in session from Task 1.

1. Navigate to `/dashboard`.
2. Use `browser_evaluate` to dispatch a synthetic wheel event over the viewport and confirm the scale changes, e.g.:
   ```js
   () => {
     const el = document.querySelector('[data-pv-viewport]');
     const rect = el.getBoundingClientRect();
     el.dispatchEvent(new WheelEvent('wheel', {
       clientX: rect.left + rect.width / 2,
       clientY: rect.top + rect.height / 2,
       deltaY: -100,
       bubbles: true,
       cancelable: true,
     }));
     return document.querySelector('[data-pv-canvas]').style.transform;
   }
   ```
   Expected: scale increased from whatever it was (call `pvResetView(<block>)` first via `browser_evaluate` if you want a known starting point of 1).
3. Confirm scrolling the mouse wheel while hovering the map does **not** scroll the underlying dashboard page (the page's scroll position should stay put — check via `browser_evaluate(() => window.scrollY)` before/after).
4. Confirm wheel-scrolling while the cursor is **outside** the map (e.g., over the page background) still scrolls the page normally (the listener must not intercept wheel events elsewhere — it checks `e.target.closest('[data-pv-viewport]')` and returns early otherwise).

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard.blade.php
git commit -m "feat: mouse-wheel zoom for PV module map"
```

---

### Task 3: Mouse drag-to-pan

**Files:**
- Modify: `resources/views/dashboard.blade.php`

**Interfaces:**
- Consumes: `pvGetView(block)`, `pvApplyTransform(block)` (from Task 1).
- Produces: `mousedown`/`mousemove`/`mouseup` listeners, a capture-phase `click` listener, `PV_DRAG_THRESHOLD` constant. No new named functions exposed to later tasks (Task 4 duplicates the same threshold constant for touch, since touch and mouse pan-state are tracked separately).

- [ ] **Step 1: Add the pan listeners**

Find (the wheel listener block added in Task 2, right before the closing `</script>`):

```blade
        const factor = e.deltaY < 0 ? PV_ZOOM_STEP : 1 / PV_ZOOM_STEP;
        pvZoomAt(block, factor, cx, cy);
    }, { passive: false });
</script>
@endpush
```

Replace with:

```blade
        const factor = e.deltaY < 0 ? PV_ZOOM_STEP : 1 / PV_ZOOM_STEP;
        pvZoomAt(block, factor, cx, cy);
    }, { passive: false });

    // Mouse drag-to-pan (background only — never on a .pv-asset, so the
    // existing Atur Posisi native drag-and-drop and normal click-through
    // to an asset's detail page are unaffected).
    const PV_DRAG_THRESHOLD = 4;
    let pvPan = null; // { block, viewport, startX, startY, origX, origY, moved }
    let pvSuppressClick = false;

    document.addEventListener('mousedown', e => {
        const viewport = e.target.closest('[data-pv-viewport]');
        if (!viewport) return;
        if (e.target.closest('.pv-asset')) return;
        const block = viewport.dataset.pvViewport;
        const v = pvGetView(block);
        pvPan = { block, viewport, startX: e.clientX, startY: e.clientY, origX: v.x, origY: v.y, moved: false };
    });

    document.addEventListener('mousemove', e => {
        if (!pvPan) return;
        const dx = e.clientX - pvPan.startX;
        const dy = e.clientY - pvPan.startY;
        if (!pvPan.moved && Math.hypot(dx, dy) < PV_DRAG_THRESHOLD) return;
        pvPan.moved = true;
        pvPan.viewport.style.cursor = 'grabbing';
        const v = pvGetView(pvPan.block);
        v.x = pvPan.origX + dx;
        v.y = pvPan.origY + dy;
        pvApplyTransform(pvPan.block);
    });

    document.addEventListener('mouseup', () => {
        if (pvPan) {
            pvPan.viewport.style.cursor = '';
            if (pvPan.moved) pvSuppressClick = true;
        }
        pvPan = null;
    });

    // A pan that actually moved would otherwise still fire a native click on
    // mouseup's target (e.g. re-opening the Edit Data modal for whatever
    // empty cell the drag ended over) — swallow exactly that one click.
    document.addEventListener('click', e => {
        if (pvSuppressClick) {
            pvSuppressClick = false;
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);
</script>
@endpush
```

- [ ] **Step 2: Manual browser verification**

Reuse the running dev server and logged-in session.

1. Navigate to `/dashboard`.
2. Use `browser_evaluate` to simulate a background drag and confirm the canvas moved:
   ```js
   () => {
     const viewport = document.querySelector('[data-pv-viewport]');
     const rect = viewport.getBoundingClientRect();
     // Pick a point away from any module cell — near the viewport's edge padding.
     const startX = rect.left + 5, startY = rect.top + 5;
     viewport.dispatchEvent(new MouseEvent('mousedown', { clientX: startX, clientY: startY, bubbles: true }));
     document.dispatchEvent(new MouseEvent('mousemove', { clientX: startX + 40, clientY: startY + 20, bubbles: true }));
     document.dispatchEvent(new MouseEvent('mouseup', { clientX: startX + 40, clientY: startY + 20, bubbles: true }));
     return document.querySelector('[data-pv-canvas]').style.transform;
   }
   ```
   Expected: `translate(...)` values shifted by roughly `(40, 20)` from wherever they were before (call `pvResetView` first for a clean baseline of `translate(0px, 0px)`).
3. Confirm a **plain click** (no movement) on an empty dashed "add asset" cell still opens the Edit Data modal when Edit Data mode is on (mousedown+mouseup at the same coordinates, no intervening mousemove — the 4px threshold is never crossed, so `pvPan.moved` stays `false` and the click is never suppressed).
4. Confirm a **plain click** on a filled module link (normal mode, Edit Data off) still navigates to `/assets/{id}` — the `.pv-asset` check exempts it from pan tracking entirely.
5. Turn on "✎ Atur Posisi" and confirm dragging a module onto another cell still performs the visual swap exactly as before (this is the regression check that matters most for this task — the native HTML5 `dragstart` must still fire because `mousedown` on a `.pv-asset` element returns early and never calls `pvPan = {...}`, so nothing here calls `preventDefault()` on it).

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard.blade.php
git commit -m "feat: mouse drag-to-pan for PV module map"
```

---

### Task 4: Touch support (one-finger pan, two-finger pinch-zoom)

**Files:**
- Modify: `resources/views/dashboard.blade.php`

**Interfaces:**
- Consumes: `pvGetView(block)`, `pvApplyTransform(block)`, `pvZoomAt(block, factor, cx, cy)`, `pvClampScale(scale)` (from Task 1), `PV_DRAG_THRESHOLD` (from Task 3).
- Produces: `touchstart`/`touchmove`/`touchend` listeners. No new named functions exposed to later tasks (this is the last task).

- [ ] **Step 1: Add the touch listeners**

Find (the end of the script from Task 3, right before the closing `</script>`):

```blade
    document.addEventListener('click', e => {
        if (pvSuppressClick) {
            pvSuppressClick = false;
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);
</script>
@endpush
```

Replace with:

```blade
    document.addEventListener('click', e => {
        if (pvSuppressClick) {
            pvSuppressClick = false;
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);

    // Touch: one-finger pan, two-finger pinch-zoom.
    let pvTouchPan = null; // same shape as pvPan
    let pvPinch = null; // { block, startDist, startScale, midX, midY }

    function pvTouchDist(t0, t1) {
        return Math.hypot(t1.clientX - t0.clientX, t1.clientY - t0.clientY);
    }

    document.addEventListener('touchstart', e => {
        const viewport = e.target.closest('[data-pv-viewport]');
        if (!viewport) return;
        const block = viewport.dataset.pvViewport;

        if (e.touches.length === 1) {
            if (e.target.closest('.pv-asset')) return;
            const v = pvGetView(block);
            const t = e.touches[0];
            pvTouchPan = { block, viewport, startX: t.clientX, startY: t.clientY, origX: v.x, origY: v.y, moved: false };
        } else if (e.touches.length === 2) {
            pvTouchPan = null;
            const v = pvGetView(block);
            const rect = viewport.getBoundingClientRect();
            const [t0, t1] = e.touches;
            pvPinch = {
                block,
                startDist: pvTouchDist(t0, t1),
                startScale: v.scale,
                midX: (t0.clientX + t1.clientX) / 2 - rect.left,
                midY: (t0.clientY + t1.clientY) / 2 - rect.top,
            };
        }
    }, { passive: true });

    document.addEventListener('touchmove', e => {
        if (pvPinch && e.touches.length === 2) {
            const [t0, t1] = e.touches;
            const dist = pvTouchDist(t0, t1);
            const v = pvGetView(pvPinch.block);
            const targetScale = pvClampScale(pvPinch.startScale * (dist / pvPinch.startDist));
            const factor = targetScale / v.scale;
            pvZoomAt(pvPinch.block, factor, pvPinch.midX, pvPinch.midY);
            e.preventDefault();
            return;
        }
        if (pvTouchPan && e.touches.length === 1) {
            const t = e.touches[0];
            const dx = t.clientX - pvTouchPan.startX;
            const dy = t.clientY - pvTouchPan.startY;
            if (!pvTouchPan.moved && Math.hypot(dx, dy) < PV_DRAG_THRESHOLD) return;
            pvTouchPan.moved = true;
            const v = pvGetView(pvTouchPan.block);
            v.x = pvTouchPan.origX + dx;
            v.y = pvTouchPan.origY + dy;
            pvApplyTransform(pvTouchPan.block);
            e.preventDefault();
        }
    }, { passive: false });

    document.addEventListener('touchend', () => {
        pvTouchPan = null;
        pvPinch = null;
    });
</script>
@endpush
```

- [ ] **Step 2: Manual browser verification**

Reuse the running dev server and logged-in session. Real touch hardware isn't available in this environment, so verify by dispatching synthetic `TouchEvent`s via `browser_evaluate` (Chromium supports constructing `Touch`/`TouchEvent` objects when launched with touch emulation; if `browser_evaluate` reports `TouchEvent is not a constructor` in the current browser context, note that limitation in your task report instead of forcing it — the mouse/wheel paths already got full real-interaction coverage in Tasks 2–3, and the touch logic is a direct structural mirror of the already-verified mouse logic).

1. Navigate to `/dashboard`, call `pvResetView('<active-block>')` via `browser_evaluate` for a clean baseline.
2. One-finger pan:
   ```js
   () => {
     const viewport = document.querySelector('[data-pv-viewport]');
     const rect = viewport.getBoundingClientRect();
     const mk = (x, y) => new Touch({ identifier: 1, target: viewport, clientX: x, clientY: y });
     const startX = rect.left + 5, startY = rect.top + 5;
     viewport.dispatchEvent(new TouchEvent('touchstart', { touches: [mk(startX, startY)], bubbles: true, cancelable: true }));
     document.dispatchEvent(new TouchEvent('touchmove', { touches: [mk(startX + 30, startY + 15)], bubbles: true, cancelable: true }));
     document.dispatchEvent(new TouchEvent('touchend', { touches: [], bubbles: true, cancelable: true }));
     return document.querySelector('[data-pv-canvas]').style.transform;
   }
   ```
   Expected: `translate(...)` shifted by roughly `(30, 15)`.
3. Two-finger pinch-zoom: dispatch `touchstart` with two `Touch` points a known distance apart, then `touchmove` with the same two points twice as far apart, and confirm the canvas `scale(...)` roughly doubled (clamped at 3).
4. Confirm one-finger touch starting on a `.pv-asset` element does not initiate panning (the `e.target.closest('.pv-asset')` check) — this preserves normal tap-to-navigate on touch devices.

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard.blade.php
git commit -m "feat: touch pan and pinch-zoom for PV module map"
```

---

## Post-implementation notes

- The dev server started for manual verification (`php artisan serve --port=8099`) should be stopped once the branch's tasks are all verified: find and kill it (`lsof -ti :8099 | xargs kill`), or simply let it be — it doesn't affect the committed code either way.
- If a future task needs the pan/zoom state to survive a page reload (e.g. bookmarkable deep-zoom links), that would be a separate follow-up — explicitly out of scope here per the design spec.

## Self-Review

- **Spec coverage:** wheel zoom ✓ (Task 2), button zoom ✓ (Task 1), background-only mouse pan with click-preserving threshold ✓ (Task 3), touch pan + pinch ✓ (Task 4), 30–300% clamp ✓ (Task 1's `pvClampScale`, used by every zoom path), per-block memory ✓ (Task 1's `window.pvMapView` keyed by block, never reset except via `pvResetView`), Atur Posisi / Edit Data / normal click-through compatibility ✓ (explicit `.pv-asset` exemption in Tasks 3–4, explicit regression checks in every task's manual verification step), no backend changes ✓ (single Blade file only).
- **Placeholder scan:** no TBD/TODO; every step has complete, concrete code.
- **Type consistency:** `pvGetView`/`pvApplyTransform`/`pvZoomAt`/`pvClampScale` signatures introduced in Task 1 are called identically (same name, same argument order) in Tasks 2–4. `PV_DRAG_THRESHOLD` introduced in Task 3 is reused (not redefined) in Task 4. `data-pv-viewport`/`data-pv-canvas` attribute names introduced in Task 1 are the only DOM hooks every later task queries.
