<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-[#697FAE] text-white border border-transparent rounded-[10px] font-semibold text-sm hover:bg-[#586D9B] active:bg-[#475980] focus:outline-none focus:ring-2 focus:ring-[#697FAE] focus:ring-offset-2 transition-all duration-150 shadow-xs select-none']) }}>
    {{ $slot }}
</button>
