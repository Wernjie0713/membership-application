@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-uber-black focus:ring-uber-black rounded-lg shadow-sm text-uber-black transition-colors duration-200']) }}>
