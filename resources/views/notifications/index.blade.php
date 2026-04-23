@extends('layouts.app')
@section('title', 'Notifications')

@section('breadcrumb')
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 font-medium">Notifications</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Notifications</h1>
        @if($notifications->where('is_read', false)->count() > 0)
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="text-sm text-brand hover:underline font-medium">
                Mark all as read
            </button>
        </form>
        @endif
    </div>

    @if($notifications->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
        </div>
        <p class="text-gray-500 font-medium">No notifications yet</p>
        <p class="text-sm text-gray-400 mt-1">You're all caught up!</p>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
        @foreach($notifications as $notification)
        <div class="flex items-start gap-4 px-5 py-4 hover:bg-opacity-90 transition-colors {{ $notification->is_read ? '' : 'bg-blue-50/40' }}">
            {{-- Type icon --}}
            <div class="flex-shrink-0 mt-0.5">
                @php
                    $iconMap = [
                        'work_order'    => ['path' => 'M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11', 'bg' => 'bg-blue-100', 'text' => 'text-brand'],
                        'maintenance'   => ['path' => 'M8 2v4 M16 2v4 M3 10h18 M3 6h18v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6z', 'bg' => 'bg-green-100', 'text' => 'text-green-600'],
                        'spare_part'    => ['path' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 6v6l4 2', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
                        'alert'         => ['path' => 'm21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z M12 9v4 M12 17h.01', 'bg' => 'bg-red-100', 'text' => 'text-red-600'],
                    ];
                    $icon = $iconMap[$notification->type] ?? ['path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z', 'bg' => 'bg-gray-100', 'text' => 'text-gray-500'];
                @endphp
                <div class="w-9 h-9 rounded-full {{ $icon['bg'] }} flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $icon['text'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="{{ $icon['path'] }}"/>
                    </svg>
                </div>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 {{ $notification->is_read ? '' : '' }}">
                            {{ $notification->title }}
                        </p>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $notification->message }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if(!$notification->is_read)
                        <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1"></span>
                        <form action="{{ route('notifications.mark-read', $notification) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-brand hover:underline whitespace-nowrap">Mark read</button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">Read</span>
                        @endif
                    </div>
                </div>
                @if($notification->url)
                <a href="{{ $notification->url }}" class="inline-flex items-center gap-1 mt-2 text-xs font-medium text-brand hover:underline">
                    View details
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="flex justify-center">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection
