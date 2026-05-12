<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-full font-medium text-sm text-uber-black hover:bg-hover-gray active:scale-[0.98] hover:-translate-y-[1px] hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-uber-black focus:ring-offset-2 disabled:opacity-25 transition-all ease-out duration-300']) }}>
    {{ $slot }}
</button>
