@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white/50 border-gray-200 rounded-2xl p-3.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold transition-all shadow-sm']) }}>
