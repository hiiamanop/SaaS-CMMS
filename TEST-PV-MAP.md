# PV Map Feature — Testing Guide

## Setup

1. **Run migration:**
   ```bash
   php artisan migrate
   ```
   Verifies: `pv_maps` table created

2. **Verify CSV exists:**
   ```bash
   ls docs/T01-249_PV_Layout.csv docs/T02-249_PV_Layout.csv
   ```
   Expected: Both files exist (250 rows each)

## Test Workflow

### Step 1: Access Settings > Lokasi PLTS
- Login as admin
- Navigate: Settings → Lokasi PLTS tab
- Should see table with locations

### Step 2: Click "Peta" Button
- Each location row has blue "Peta" button
- Click button → Modal opens with "Upload File CSV" interface

### Step 3: Upload CSV
- Drag-drop or click to select `docs/T01-249_PV_Layout.csv`
- Preview shows:
  - Transformer Block: T01
  - Module Count: 249
  - "Lanjutkan Preview" button enabled

### Step 4: Preview Grid
- Click "Lanjutkan Preview"
- Stage changes to "Edit"
- Grid layout shows (24x24 columns grid visualization)
- Each module appears as blue box with code (e.g., "INV04-S01")

### Step 5: Edit Module Position
- Click any module box in grid
- "Edit Posisi" form appears with:
  - Module code (read-only)
  - Status dropdown (active/inactive/replaced)
  - Visual Row input (1-24)
  - Visual Col input (1-24)
- Change values → grid updates in real-time

### Step 6: Save Map
- Click "Simpan Peta" button
- Loader shows "Menyimpan..."
- On success: alert "Peta berhasil disimpan!"
- Page reloads

### Step 7: Verify Database
```bash
# Check pv_maps table
php artisan tinker
>>> App\Models\PvMap::all();
# Should show: id, location_id, transformer_block, map_status (published), created_at

# Check Assets updated
>>> App\Models\Asset::where('transformer_block', 'T01')->where('category', 'PV Module')->select('asset_code', 'visual_row', 'visual_col')->take(5)->get();
# Should show visual_row/visual_col with values from CSV
```

## Expected Results

| Check | Expected | Status |
|-------|----------|--------|
| Migration runs | pv_maps table exists | ✓ |
| CSV upload | Modal accepts file | ✓ |
| Preview displays | Grid shows 249 modules | ✓ |
| Edit functionality | Can change visual_row/col | ✓ |
| Save to DB | PvMap created, Assets updated | ✓ |
| Update on re-upload | T01 re-upload updates existing | ✓ |

## Troubleshooting

**Modal not opening:**
- Check console for Alpine JS errors
- Verify Chrome/Firefox latest version
- Clear browser cache

**CSV upload fails:**
- File must start with T01/T02/etc
- Format: 5 columns (transformer_block, string_number, module_slot, visual_row, visual_col)
- No empty rows

**Grid not rendering:**
- Check browser console for JS errors
- Verify Tailwind CSS loaded

## Notes

- Single CSV per upload (one transformer block per file)
- Update existing: re-upload same transformer block → overwrites
- Draft mode not used yet (all saves go to "published")
