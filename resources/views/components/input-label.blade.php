@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-uber-black']) }}>
    {{ $value ?? $slot }}
</label>
