@props([
    'variant' => 'default', // default (primary), secondary, outline, ghost, destructive, link
    'size'    => 'default', // sm, default, lg, icon
    'as'      => 'button',  // button, a
    'href'    => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C767] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 select-none';

    $variants = [
        'default'     => 'bg-[#00C767] text-[#011E0F] hover:bg-[#00B05B] font-semibold shadow-xs',
        'secondary'   => 'bg-[#F7F7F7] text-[#171717] hover:bg-[#EAEAEA] border border-[#E5E5E5]',
        'outline'     => 'border border-[#E5E5E5] bg-white text-[#171717] hover:bg-[#F7F7F7]',
        'ghost'       => 'text-[#171717] hover:bg-[#F7F7F7]',
        'destructive' => 'bg-[#D92D20] text-white hover:bg-[#B91C1C] font-semibold shadow-xs',
        'link'        => 'text-[#00C767] underline-offset-4 hover:underline font-medium p-0 h-auto',
    ];

    $sizes = [
        'default' => 'h-[46px] px-5 py-2.5 text-sm rounded-[10px]',
        'sm'      => 'h-9 px-3.5 text-xs rounded-[8px]',
        'lg'      => 'h-[50px] px-7 text-base rounded-[10px]',
        'icon'    => 'h-10 w-10 p-0 rounded-[10px]',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['default']);
@endphp

@if($as === 'a' || $href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => $attributes->get('type', 'button')]) }}>
        {{ $slot }}
    </button>
@endif
