<section>
    <header>
        <h2 class="text-lg font-medium theme-text-primary">
            {{ __('Site Logo') }}
        </h2>
        <p class="mt-1 text-sm theme-text-secondary">
            {{ __('Logo shown on the login page and in the sidebar. Leave empty to use the app name.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.logo.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        @if($currentLogoUrl)
            <div class="flex items-center gap-4">
                <img src="{{ $currentLogoUrl }}" alt="{{ config('app.name') }}" class="h-12 w-auto object-contain rounded-lg border theme-border theme-bg-tertiary/50 p-1">
                <span class="text-sm theme-text-secondary">{{ __('Current logo') }}</span>
            </div>
        @endif

        <div>
            <x-input-label for="logo" :value="__('Upload new logo')" />
            <input id="logo" name="logo" type="file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/svg+xml" class="mt-1 block w-full text-sm theme-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-orange-500/20 file:text-orange-400 hover:file:bg-orange-500/30 file:cursor-pointer">
            <x-input-error class="mt-2" :messages="$errors->get('logo')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save logo') }}</x-primary-button>
            @if (session('status') === 'logo-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm theme-text-secondary"
                >{{ __('Logo updated.') }}</p>
            @endif
        </div>
    </form>
</section>
