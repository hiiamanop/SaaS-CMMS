<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
    h1 { font-size: 16px; margin: 0 0 2px; }
    h2 { font-size: 13px; margin: 16px 0 6px; border-bottom: 1px solid #999; padding-bottom: 2px; }
    table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
    th { background: #f0f0f0; }
    .muted { color: #888; }
</style>
</head>
<body>
    <h1>Laporan Bulanan</h1>
    <div class="muted">{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</div>

    <h2>Work Orders ({{ $workOrders->count() }})</h2>
    <table>
        <thead><tr><th>No</th><th>Title</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($workOrders as $wo)
            <tr><td>{{ $wo->wo_number }}</td><td>{{ $wo->title }}</td><td>{{ $wo->status }}</td></tr>
        @empty<tr><td colspan="3" class="muted">None</td></tr>@endforelse
        </tbody>
    </table>

    <h2>Maintenance Records ({{ $records->count() }})</h2>
    <table>
        <thead><tr><th>No</th><th>Asset</th><th>Date</th><th>Result</th></tr></thead>
        <tbody>
        @forelse($records as $r)
            <tr><td>{{ $r->record_number }}</td><td>{{ $r->asset->name ?? '—' }}</td><td>{{ $r->maintenance_date->format('d M Y') }}</td><td>{{ $r->status_after }}</td></tr>
        @empty<tr><td colspan="4" class="muted">None</td></tr>@endforelse
        </tbody>
    </table>

    <h2>Checksheets ({{ $checksheets->count() }})</h2>
    <table>
        <thead><tr><th>Session</th><th>Submitted</th></tr></thead>
        <tbody>
        @forelse($checksheets as $cs)
            <tr><td>{{ $cs->schedule->trafo_name ?? ('#'.$cs->id) }}</td><td>{{ optional($cs->submitted_at)->format('d M Y') }}</td></tr>
        @empty<tr><td colspan="2" class="muted">None</td></tr>@endforelse
        </tbody>
    </table>

    <h2>Findings ({{ $findings->count() }})</h2>
    <table>
        <thead><tr><th>Title</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($findings as $f)
            <tr><td>{{ $f->title }}</td><td>{{ $f->status }}</td></tr>
        @empty<tr><td colspan="2" class="muted">None</td></tr>@endforelse
        </tbody>
    </table>

    <h2>Barang Terpakai</h2>
    <table>
        <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>Value</th></tr></thead>
        <tbody>
        @foreach($spareParts as $it)
            <tr><td>{{ $it['name'] }}</td><td>Spare Part</td><td>{{ $it['qty'] }} {{ $it['unit'] }}</td><td>IDR {{ number_format($it['value']) }}</td></tr>
        @endforeach
        @foreach($consumables as $it)
            <tr><td>{{ $it['name'] }}</td><td>Consumable</td><td>{{ $it['qty'] }} {{ $it['unit'] }}</td><td>IDR {{ number_format($it['value']) }}</td></tr>
        @endforeach
        @foreach($tools as $it)
            <tr><td>{{ $it['name'] }}</td><td>Tool</td><td>{{ $it['count'] }}x</td><td>—</td></tr>
        @endforeach
        @if($spareParts->isEmpty() && $consumables->isEmpty() && $tools->isEmpty())
            <tr><td colspan="4" class="muted">No items used this month</td></tr>
        @endif
        </tbody>
    </table>
</body>
</html>
