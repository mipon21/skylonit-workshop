@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'rounded-xl theme-input-bg theme-input-border border theme-text-primary px-4 py-2.5 theme-input-focus w-full']) !!}>
