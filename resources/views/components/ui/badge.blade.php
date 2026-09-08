@props([
    'variant' => 'default', // default (primary green), secondary, outline, destructive, success, warning
])

@php
    $baseClasses = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-[#00C767] focus:ring-offset-2 select-none';

    $variants = [
        'default'     => 'bg-[#E6FBF0] text-[#011E0F] border border-[#00C767]/30',
        'secondary'   => 'bg-[#F7F7F7] text-[#171717] border border-[#E5E5E5]',
        'outline'     => 'text-[#171717] border border-[#E5E5E5] bg-white',
        'destructive' => 'bg-red-50 text-[#D92D20] border border-red-200',
        'success'     => 'bg-[#00C767] text-[#011E0F] font-bold shadow-xs',
        'warning'     => 'bg-amber-50 text-amber-800 border border-amber-200',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
