<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-[#00C767] text-[#011E0F] border border-transparent rounded-[10px] font-semibold text-sm hover:bg-[#00B05B] active:bg-[#009E51] focus:outline-none focus:ring-2 focus:ring-[#00C767] focus:ring-offset-2 transition-all duration-150 shadow-xs select-none']) }}>
    {{ $slot }}
</button>
