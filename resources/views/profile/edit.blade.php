<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if($isClient ?? false)
                {{-- Client: only personal data, password, and delete account --}}
                @if($client)
                <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                    <div class="max-w-xl">
                        @include('profile.partials.update-client-information-form')
                    </div>
                </div>
                @else
                <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                    <p class="text-slate-400 text-sm">Your account is not linked to a client record. Contact support if you need to update your details.</p>
                </div>
                @endif
                <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            @elseif(($isDeveloper ?? false) || ($isSales ?? false))
                {{-- Developer / Sales: profile info, password, payment methods (visible only to Admin) --}}
                <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                    <h3 class="text-lg font-semibold text-white mb-2">Payment methods</h3>
                    <p class="text-slate-500 text-sm mb-4">Visible only to Admin. Add bank or mobile banking details for payouts.</p>
                    <ul class="space-y-3 mb-4">
                        @forelse($paymentMethods as $pm)
                            <li class="flex items-center justify-between gap-3 py-2 border-b border-slate-700/50">
                                <span class="text-slate-300">{{ $pm->label ?: ucfirst($pm->type) }}</span>
                                <form action="{{ route('profile.payment-methods.destroy', $pm) }}" method="POST" class="inline" onsubmit="return confirm('Remove this payment method?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                                </form>
                            </li>
                        @empty
                            <li class="text-slate-500 text-sm">No payment methods added.</li>
                        @endforelse
                    </ul>
                    <form action="{{ route('profile.payment-methods.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Type</label>
                            <select name="type" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500">
                                @foreach(\App\Models\UserPaymentMethod::typeOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Label (optional)</label>
                            <input type="text" name="label" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500" placeholder="e.g. Bkash personal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Details (account number, bank name, etc.)</label>
                            <textarea name="details" rows="2" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500" placeholder="Account details"></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium">Add</button>
                    </form>
                </div>
            @else
                {{-- Admin: full profile (info, logo, favicon, password) --}}
                <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                    <div class="max-w-xl">
                        @include('profile.partials.update-logo-form')
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                    <div class="max-w-xl">
                        @include('profile.partials.update-favicon-form')
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
