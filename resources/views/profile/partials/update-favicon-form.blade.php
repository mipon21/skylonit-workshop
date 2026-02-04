<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Favicon') }}
        </h2>
        <p class="mt-1 text-sm text-slate-400">
            {{ __('Icon shown in the browser tab. Use .ico, .png, .gif or .svg (small size recommended).') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.favicon.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        @if($currentFaviconUrl)
            <div class="flex items-center gap-4">
                <img src="{{ $currentFaviconUrl }}" alt="Favicon" class="h-8 w-8 object-contain rounded border border-slate-600 bg-slate-800/50 p-1">
                <span class="text-sm text-slate-400">{{ __('Current favicon') }}</span>
            </div>
        @endif

        <div>
            <x-input-label for="favicon" :value="__('Upload new favicon')" />
            <input id="favicon" name="favicon" type="file" accept=".ico,image/png,image/gif,image/svg+xml" class="mt-1 block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-sky-500/20 file:text-sky-400 hover:file:bg-sky-500/30 file:cursor-pointer">
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
                    class="text-sm text-slate-400"
                >{{ __('Favicon updated.') }}</p>
            @endif
        </div>
    </form>
</section>
