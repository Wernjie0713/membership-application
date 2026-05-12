<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-uber-black border border-transparent rounded-full font-medium text-sm text-white hover:bg-uber-black/90 focus:bg-uber-black/90 active:bg-black active:scale-[0.98] hover:-translate-y-[1px] hover:shadow-uber-float focus:outline-none focus:ring-2 focus:ring-uber-black focus:ring-offset-2 transition-all ease-out duration-300']) }}>
    {{ $slot }}
</button>
