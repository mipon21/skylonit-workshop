@if($payments->count() > 0)
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 max-md:grid-cols-1 max-md:gap-3">
        @foreach($payments as $payment)
            <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-5 flex flex-col max-md:p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold text-white">{{ $payment->project->project_name }}</p>
                        @if($payment->project->project_code)
                            <p class="text-slate-400 text-sm">{{ $payment->project->project_code }}</p>
                        @endif
                    </div>
                    <span @class([
                        'shrink-0 px-2.5 py-1 rounded-lg text-xs font-medium',
                        'bg-amber-500/20 text-amber-400 border border-amber-500/40' => $payment->isDue(),
                        'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' => $payment->isPaid(),
                    ])>{{ $payment->payment_status }}</span>
                </div>
                <p class="text-xl font-bold text-white mt-2">৳ {{ number_format($payment->amount, 0) }}</p>
                @if($payment->payment_type)
                    <p class="text-slate-500 text-sm mt-0.5">{{ ucfirst($payment->payment_type) }} payment</p>
                @endif
                <p class="text-slate-500 text-xs mt-2">Created {{ $payment->created_at->format('M d, Y') }}</p>
                <div class="mt-4 pt-4 border-t border-slate-700/50 flex flex-col gap-2">
                    @if($payment->isDue() && $payment->payment_link)
                        <a href="{{ $payment->payment_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm transition max-md:w-full">Pay Now</a>
                    @endif
                    @if($payment->isPaid())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 text-sm font-medium">Paid</span>
                        @if($payment->invoice)
                            <a href="{{ route('invoices.download', $payment->invoice) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 text-sm font-medium transition max-md:w-full">Download Invoice</a>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @if($payments->hasPages())
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl px-6 py-4">{{ $payments->links() }}</div>
    @endif
@else
    <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-700/50 mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No Payments Yet</h3>
        <p class="text-slate-400">Payments for your projects will appear here. Use "Pay Now" to pay online when a payment is due.</p>
    </div>
@endif
