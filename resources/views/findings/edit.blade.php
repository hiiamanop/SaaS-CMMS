@extends('layouts.app')
@section('title', 'Edit Finding')
@section('breadcrumb')
<span class="text-gray-400">/</span><a href="{{ route('findings.index') }}" class="hover:text-gray-800">Findings</a>
<span class="text-gray-400">/</span><a href="{{ route('findings.show', $finding) }}" class="hover:text-gray-800">{{ Str::limit($finding->title, 30) }}</a>
<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Edit</span>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Edit Finding</h2>
        <form method="POST" action="{{ route('findings.update', $finding) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('findings._form', ['finding' => $finding])
            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all shadow-sm">Update</button>
                <a href="{{ route('findings.show', $finding) }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-all">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
