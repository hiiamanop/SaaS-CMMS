@extends('layouts.app')
@section('title','Assets')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Assets</span>@endsection
@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div><h1 class="text-2xl font-bold text-gray-900">Assets</h1><p class="text-sm text-gray-500 mt-0.5">Manage your equipment and machinery</p></div>
        @if(!auth()->user()->isTechnician())
        <a href="{{ route('assets.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>Add Asset
        </a>
        @endif
    </div>
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Search assets..." class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
            <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">All Categories</option>
                @foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ $cat }}</option>@endforeach
            </select>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
                <option value="under_maintenance" {{ request('status')=='under_maintenance'?'selected':'' }}>Under Maintenance</option>
                <option value="retired" {{ request('status')=='retired'?'selected':'' }}>Retired</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Filter</button>
            <a href="{{ route('assets.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Reset</a>
        </form>
    </div>
    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($assets->isEmpty())
        <div class="py-16 text-center"><svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg><p class="text-gray-400 font-medium">No assets found</p></div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Asset Code</th><th class="px-5 py-3 text-left">Name</th><th class="px-5 py-3 text-left">Category</th><th class="px-5 py-3 text-left">Location</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Brand / Model</th><th class="px-5 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($assets as $asset)
            @php $sc=['active'=>'bg-green-100 text-green-700','inactive'=>'bg-gray-100 text-gray-600','under_maintenance'=>'bg-yellow-100 text-yellow-700','retired'=>'bg-red-100 text-red-600']; @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $asset->asset_code }}</td>
                <td class="px-5 py-3"><a href="{{ route('assets.show', $asset) }}" class="font-medium text-gray-900 hover:text-brand">{{ $asset->name }}</a></td>
                <td class="px-5 py-3 text-gray-500">{{ $asset->category }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $asset->location }}</td>
                <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$asset->status]??'bg-gray-100 text-gray-600' }}">{{ ucwords(str_replace('_',' ',$asset->status)) }}</span></td>
                <td class="px-5 py-3 text-gray-500">{{ $asset->brand }} {{ $asset->model }}</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('assets.show', $asset) }}" class="p-1.5 text-gray-400 hover:text-brand hover:bg-blue-50 rounded-lg transition-colors" title="View"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></a>
                        @if(!auth()->user()->isTechnician())
                        <a href="{{ route('assets.edit', $asset) }}" class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                        <button @click="$dispatch('open-delete',{action:'{{ route('assets.destroy',$asset) }}',message:'Delete asset {{ addslashes($asset->name) }}? This cannot be undone.'})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $assets->links() }}</div>
        @endif
    </div>
</div>
@endsection
