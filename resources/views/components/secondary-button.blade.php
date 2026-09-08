<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-white border border-[#E5E5E5] rounded-[10px] font-medium text-sm text-[#171717] shadow-xs hover:bg-[#F7F7F7] focus:outline-none focus:ring-2 focus:ring-[#00C767] focus:ring-offset-2 disabled:opacity-50 transition-all duration-150 select-none']) }}>
    {{ $slot }}
</button>
