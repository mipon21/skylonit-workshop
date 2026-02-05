<div x-show="paymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="paymentModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="paymentModal = false"></div>
        <div x-show="paymentModal" x-transition class="relative w-full max-w-sm bg-slate-800 border border-slate-700 rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold text-white mb-4">Add Payment</h2>
            <form action="{{ route('projects.payments.store', $project) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Payment type *</label>
                        <select name="payment_type" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            @foreach($project->availablePaymentTypes() as $value => $label)
                                <option value="{{ $value }}" {{ old('payment_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-slate-500 text-xs mt-1">Determines which invoice template is used.</p>
                        @error('payment_type')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Amount (৳) *</label>
                        <input type="number" name="amount" step="0.01" min="0" required value="{{ old('amount') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Payment method</label>
                        <select name="payment_method" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            <option value="">Select method</option>
                            @foreach(\App\Models\Payment::PAYMENT_METHODS as $method)
                                <option value="{{ $method }}" {{ old('payment_method') === $method ? 'selected' : '' }}>{{ $method }}</option>
                            @endforeach
                        </select>
                        @error('payment_method')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Date</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('payment_date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <p class="text-slate-500 text-xs">New payments are created as <strong>DUE</strong>. A UddoktaPay link is generated for the client. Use &quot;Mark as Paid (Cash)&quot; for offline payment, or the client pays via the link.</p>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Note (e.g. advance, first, second, final)</label>
                        <input type="text" name="note" value="{{ old('note') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="advance / first / second / final">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="paymentModal = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
