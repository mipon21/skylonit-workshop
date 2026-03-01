<section>
    <header>
        <h2 class="text-lg font-medium theme-text-primary">
            {{ __('Favicon') }}
        </h2>
        <p class="mt-1 text-sm theme-text-secondary">
            {{ __('Icon shown in the browser tab. Use .ico, .png, .gif or .svg (small size recommended).') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.favicon.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        @if($currentFaviconUrl)
            <div class="flex items-center gap-4">
                <img src="{{ $currentFaviconUrl }}" alt="Favicon" class="h-8 w-8 object-contain rounded border theme-border theme-bg-tertiary/50 p-1">
                <span class="text-sm theme-text-secondary">{{ __('Current favicon') }}</span>
            </div>
        @endif

        <div>
            <x-input-label for="favicon" :value="__('Upload new favicon')" />
            <input id="favicon" name="favicon" type="file" accept=".ico,image/png,image/gif,image/svg+xml" class="mt-1 block w-full text-sm theme-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-orange-500/20 file:text-orange-400 hover:file:bg-orange-500/30 file:cursor-pointer">
            <x-input-error class="mt-2" :messages="$errors->get('favicon')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save favicon') }}</x-primary-button>
            @if (session('status') === 'favicon-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm theme-text-secondary"
                >{{ __('Favicon updated.') }}</p>
            @endif
        </div>
    </form>
</section>
