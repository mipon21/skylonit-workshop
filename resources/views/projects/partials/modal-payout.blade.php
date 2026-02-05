@php
    $payoutTypes = ['overhead' => 'Overhead', 'sales' => 'Sales', 'developer' => 'Developer', 'profit' => 'Profit'];
@endphp
<div x-show="payoutModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="payoutModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="payoutModal = false"></div>
        <div x-show="payoutModal" x-transition class="relative w-full max-w-md bg-slate-800 border border-slate-700 rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold text-white mb-4" x-text="payoutType ? 'Payout: ' + payoutType.charAt(0).toUpperCase() + payoutType.slice(1) : 'Payout'"></h2>

            @foreach($payoutTypes as $type => $label)
                @php $p = $project->getPayoutFor($type); @endphp
                <form action="{{ route('projects.payouts.update', $project) }}" method="POST" x-show="payoutType === '{{ $type }}'" x-transition class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Status *</label>
                            <select name="status" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                <option value="not_paid" {{ ($p?->status ?? 'not_paid') === 'not_paid' ? 'selected' : '' }}>Not Paid</option>
                                <option value="upcoming" {{ ($p?->status ?? '') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                <option value="due" {{ ($p?->status ?? '') === 'due' ? 'selected' : '' }}>Due</option>
                                <option value="partial" {{ ($p?->status ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ ($p?->status ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Amount paid (৳)</label>
                            <input type="number" name="amount_paid" step="0.01" min="0" value="{{ $p?->amount_paid ?? '' }}" placeholder="Optional" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Paid at (date)</label>
                            <input type="date" name="paid_at" value="{{ $p?->paid_at?->format('Y-m-d') ?? '' }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Note</label>
                            <textarea name="note" rows="2" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="Optional">{{ $p?->note ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="payoutModal = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Save</button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</div>
