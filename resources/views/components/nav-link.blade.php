@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-[11px] font-black uppercase tracking-widest shadow-sm border border-indigo-100/50 transition-all duration-300'
            : 'inline-flex items-center px-4 py-2 text-[11px] font-bold text-gray-400 uppercase tracking-widest hover:text-indigo-600 hover:bg-indigo-50/50 rounded-xl transition-all duration-300';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
