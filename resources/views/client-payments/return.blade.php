<x-guest-layout>
    <div class="text-center space-y-6">
        @if($success)
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-500/20 border border-emerald-500/40">
                <svg class="w-8 h-8 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-white">Payment Successful</h2>
        @else
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-500/20 border border-amber-500/40">
                <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-white">{{ $success ? 'Success' : 'Notice' }}</h2>
        @endif

        <p class="text-slate-300">{{ $message }}</p>

        @if(!empty($showManualEntry))
            <div class="mt-6 p-4 rounded-xl bg-slate-800/60 border border-slate-700/50 text-left max-w-sm mx-auto">
                <p class="text-sm text-slate-300 mb-3">If you have your <strong>transaction</strong> or <strong>invoice ID</strong> from the payment page or email, enter it below to confirm your payment:</p>
                <form method="POST" action="{{ route('client.payment.success') }}" class="space-y-3">
                    @csrf
                    <input type="text" name="invoice_id" value="{{ old('invoice_id') }}" placeholder="e.g. W0XHf6LS3m74u8QbZ0Ne"
                           class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-white placeholder-slate-500 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                    @error('invoice_id')
                        <p class="text-amber-400 text-xs">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm transition">
                        Confirm payment
                    </button>
                </form>
            </div>
        @endif

        <div class="pt-2">
            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium transition">
                Log in to client portal
            </a>
        </div>
    </div>
</x-guest-layout>
