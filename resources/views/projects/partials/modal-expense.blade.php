<div x-show="expenseModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="expenseModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="expenseModal = false"></div>
        <div x-show="expenseModal" x-transition class="relative w-full max-w-sm bg-slate-800 border border-slate-700 rounded-2xl shadow-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Add Expense</h2>
            <form action="{{ route('projects.expenses.store', $project) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Amount (৳) *</label>
                        <input type="number" name="amount" step="0.01" min="0" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Note</label>
                        <textarea name="note" rows="2" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500"></textarea>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-medium text-slate-400">Visibility</span>
                        <label class="relative inline-flex items-center cursor-pointer gap-3 flex-shrink-0">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" name="is_public" value="1" checked class="sr-only peer" id="expense-modal-is-public">
                            <span class="relative block h-6 w-11 shrink-0 rounded-full border-2 border-slate-500 bg-slate-600 transition-colors peer-focus:ring-2 peer-focus:ring-sky-500/50 peer-checked:bg-sky-500 peer-checked:border-sky-500" aria-hidden="true" style="min-width: 2.75rem; min-height: 1.5rem;"></span>
                            <span class="absolute z-10 h-4 w-4 rounded-full border-2 border-slate-400 bg-white shadow-md transition-transform duration-200 ease-out pointer-events-none peer-checked:translate-x-5" style="left: 0.25rem; top: 0.25rem; width: 1rem; height: 1rem;" aria-hidden="true"></span>
                            <span class="text-sm text-slate-300 whitespace-nowrap" id="expense-visibility-label">Public (anyone can see)</span>
                        </label>
                    </div>
                    <script>
                        (function() {
                            var cb = document.getElementById('expense-modal-is-public');
                            var label = document.getElementById('expense-visibility-label');
                            if (cb && label) {
                                cb.addEventListener('change', function() { label.textContent = this.checked ? 'Public (anyone can see)' : 'Private (admin only)'; });
                            }
                        })();
                    </script>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="expenseModal = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
