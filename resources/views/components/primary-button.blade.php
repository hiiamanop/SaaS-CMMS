<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-brand text-white border border-transparent rounded-lg font-bold text-xs uppercase tracking-widest hover:bg-brand-600 focus:bg-brand-600 active:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
