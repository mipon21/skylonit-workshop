@foreach($project->expenses as $expense)
<div x-show="expenseEditModal === {{ $expense->id }}" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="expenseEditModal === {{ $expense->id }}" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="expenseEditModal = null"></div>
        <div x-show="expenseEditModal === {{ $expense->id }}" x-transition class="relative w-full max-w-sm bg-slate-800 border border-slate-700 rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold text-white mb-4">Edit Expense</h2>
            <form action="{{ route('projects.expenses.update', [$project, $expense]) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Amount (৳) *</label>
                        <input type="number" name="amount" step="0.01" min="0" required value="{{ old('amount', $expense->amount) }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Note</label>
                        <textarea name="note" rows="2" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('note', $expense->note) }}</textarea>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-medium text-slate-400">Visibility</span>
                        <label class="relative inline-flex items-center cursor-pointer gap-3 flex-shrink-0">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" name="is_public" value="1" {{ $expense->is_public ? 'checked' : '' }} class="sr-only peer" id="expense-edit-modal-is-public-{{ $expense->id }}">
                            <span class="relative block h-6 w-11 shrink-0 rounded-full border-2 border-slate-500 bg-slate-600 transition-colors peer-focus:ring-2 peer-focus:ring-sky-500/50 peer-checked:bg-sky-500 peer-checked:border-sky-500" aria-hidden="true" style="min-width: 2.75rem; min-height: 1.5rem;"></span>
                            <span class="absolute z-10 h-4 w-4 rounded-full border-2 border-slate-400 bg-white shadow-md transition-transform duration-200 ease-out pointer-events-none peer-checked:translate-x-5" style="left: 0.25rem; top: 0.25rem; width: 1rem; height: 1rem;" aria-hidden="true"></span>
                            <span class="text-sm text-slate-300 whitespace-nowrap" id="expense-edit-visibility-label-{{ $expense->id }}">{{ $expense->is_public ? 'Public (anyone can see)' : 'Private (admin only)' }}</span>
                        </label>
                    </div>
                    <script>
                        (function() {
                            var cb = document.getElementById('expense-edit-modal-is-public-{{ $expense->id }}');
                            var label = document.getElementById('expense-edit-visibility-label-{{ $expense->id }}');
                            if (cb && label) {
                                cb.addEventListener('change', function() { label.textContent = this.checked ? 'Public (anyone can see)' : 'Private (admin only)'; });
                            }
                        })();
                    </script>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="expenseEditModal = null" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
