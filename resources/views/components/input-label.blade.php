@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1']) }}>
    {{ $value ?? $slot }}
</label>
