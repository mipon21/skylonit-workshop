<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 theme-btn-primary border border-transparent rounded-xl font-semibold text-sm focus:outline-none theme-input-focus transition']) }}>
    {{ $slot }}
</button>
