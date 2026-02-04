<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Site Logo') }}
        </h2>
        <p class="mt-1 text-sm text-slate-400">
            {{ __('Logo shown on the login page and in the sidebar. Leave empty to use the app name.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.logo.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        @if($currentLogoUrl)
            <div class="flex items-center gap-4">
                <img src="{{ $currentLogoUrl }}" alt="{{ config('app.name') }}" class="h-12 w-auto object-contain rounded-lg border border-slate-600 bg-slate-800/50 p-1">
                <span class="text-sm text-slate-400">{{ __('Current logo') }}</span>
            </div>
        @endif

        <div>
            <x-input-label for="logo" :value="__('Upload new logo')" />
            <input id="logo" name="logo" type="file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/svg+xml" class="mt-1 block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-sky-500/20 file:text-sky-400 hover:file:bg-sky-500/30 file:cursor-pointer">
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
                    class="text-sm text-slate-400"
                >{{ __('Logo updated.') }}</p>
            @endif
        </div>
    </form>
</section>
