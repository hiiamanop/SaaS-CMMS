@props([
    'disabled' => false,
])

<input {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => 'flex h-11 w-full rounded-[10px] border border-[#E5E5E5] bg-white px-3.5 py-2 text-sm text-[#171717] placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#00C767] focus:border-[#00C767] disabled:cursor-not-allowed disabled:bg-[#F7F7F7] disabled:text-[#525252] transition-all shadow-xs']) }}>
