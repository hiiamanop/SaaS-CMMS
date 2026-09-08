<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-[#D92D20] text-white border border-transparent rounded-[10px] font-semibold text-sm hover:bg-[#B91C1C] active:bg-[#991B1B] focus:outline-none focus:ring-2 focus:ring-[#D92D20] focus:ring-offset-2 transition-all duration-150 shadow-xs select-none']) }}>
    {{ $slot }}
</button>
