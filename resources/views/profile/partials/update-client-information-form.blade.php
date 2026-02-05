<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Your Information') }}
        </h2>
        <p class="mt-1 text-sm text-slate-400">
            {{ __('You can update your name and address only. Contact details are managed by your account administrator.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $client->name ?? $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="address" :value="__('Address')" />
            <textarea id="address" name="address" rows="2" class="mt-1 block w-full rounded-xl bg-slate-900 border border-slate-600 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500">{{ old('address', $client->address ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div class="pt-4 border-t border-slate-700/50 space-y-3">
            <p class="text-sm font-medium text-slate-400">{{ __('Contact details (managed by administrator)') }}</p>
            <div>
                <span class="text-xs text-slate-500">{{ __('Phone') }}</span>
                <p class="text-white mt-0.5">{{ $client->phone ?? '—' }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-500">{{ __('Email') }}</span>
                <p class="text-white mt-0.5">{{ $client->email ?? $user->email ?? '—' }}</p>
            </div>
            <div>
                <span class="text-xs text-slate-500">{{ __('Facebook Link') }}</span>
                <p class="text-white mt-0.5">
                    @if($client->fb_link ?? null)
                        <a href="{{ $client->fb_link }}" target="_blank" rel="noopener" class="text-sky-400 hover:text-sky-300">{{ Str::limit($client->fb_link, 50) }}</a>
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <span class="text-xs text-slate-500">{{ __('WhatsApp') }}</span>
                <p class="text-white mt-0.5">
                    @if($client->whatsapp_link ?? null)
                        <a href="{{ $client->whatsapp_link }}" target="_blank" rel="noopener" class="text-sky-400 hover:text-sky-300">WhatsApp</a>
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <span class="text-xs text-slate-500">{{ __('KYC') }}</span>
                <p class="text-white mt-0.5">{{ $client->kyc ?? '—' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
