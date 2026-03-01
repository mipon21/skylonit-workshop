@foreach($project->payments as $payment)
<div x-show="paymentEditModal === {{ $payment->id }}" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="paymentEditModal === {{ $payment->id }}" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="paymentEditModal = null"></div>
        <div x-show="paymentEditModal === {{ $payment->id }}" x-transition class="relative w-full max-w-sm theme-bg-tertiary border theme-border rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold theme-text-primary mb-4">Edit Payment</h2>
            <form action="{{ route('projects.payments.update', [$project, $payment]) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Amount (৳) *</label>
                        <input type="number" name="amount" step="0.01" min="0" required value="{{ old('amount', $payment->amount) }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Payment method</label>
                        <select name="payment_method" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                            <option value="">Select method</option>
                            @foreach(\App\Models\Payment::PAYMENT_METHODS as $method)
                                <option value="{{ $method }}" {{ old('payment_method', $payment->payment_method) === $method ? 'selected' : '' }}>{{ $method }}</option>
                            @endforeach
                        </select>
                        @error('payment_method')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Date</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d')) }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @error('payment_date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Status *</label>
                        <select name="status" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                            <option value="upcoming" {{ old('status', $payment->status) === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="due" {{ old('status', $payment->status) === 'due' ? 'selected' : '' }}>Due</option>
                            <option value="completed" {{ old('status', $payment->status) === 'completed' ? 'selected' : '' }}>Completed / Paid</option>
                        </select>
                        <p class="theme-text-muted text-xs mt-1">Only &quot;Completed / Paid&quot; counts toward Total paid and reduces Due.</p>
                        @error('status')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Note (e.g. advance, first, second, final)</label>
                        <input type="text" name="note" value="{{ old('note', $payment->note) }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" placeholder="advance / first / second / final">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="paymentEditModal = null" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
