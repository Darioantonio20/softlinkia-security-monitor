<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-10 py-4 bg-indigo-600 border border-transparent rounded-[1.2rem] font-black text-[10px] text-white uppercase tracking-[0.2em] shadow-[0_10px_20px_-5px_rgba(79,70,229,0.4)] hover:shadow-[0_15px_25px_-5px_rgba(79,70,229,0.5)] hover:-translate-y-0.5 active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200']) }}>
    {{ $slot }}
</button>
