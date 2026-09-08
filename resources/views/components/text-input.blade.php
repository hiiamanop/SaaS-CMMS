@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'h-11 w-full border-[#E5E5E5] focus:border-[#697FAE] focus:ring-2 focus:ring-[#697FAE] rounded-[10px] shadow-xs text-sm text-[#171717] placeholder:text-gray-400 bg-white transition-all disabled:bg-[#F7F7F7] disabled:text-[#525252]']) }}>
