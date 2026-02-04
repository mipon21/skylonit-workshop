<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-sky-500 hover:bg-sky-600 border border-transparent rounded-xl font-semibold text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500 transition']) }}>
    {{ $slot }}
</button>
