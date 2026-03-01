@foreach($project->expenses as $expense)
<div x-show="expenseEditModal === {{ $expense->id }}" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="expenseEditModal === {{ $expense->id }}" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="expenseEditModal = null"></div>
        <div x-show="expenseEditModal === {{ $expense->id }}" x-transition class="relative w-full max-w-sm theme-bg-tertiary border theme-border rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold theme-text-primary mb-4">Edit Expense</h2>
            <form action="{{ route('projects.expenses.update', [$project, $expense]) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Amount (৳) *</label>
                        <input type="number" name="amount" step="0.01" min="0" required value="{{ old('amount', $expense->amount) }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Note</label>
                        <textarea name="note" rows="2" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">{{ old('note', $expense->note) }}</textarea>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-medium theme-text-secondary">Visibility</span>
                        <label class="relative inline-flex items-center cursor-pointer gap-3 flex-shrink-0">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" name="is_public" value="1" {{ $expense->is_public ? 'checked' : '' }} class="sr-only peer" id="expense-edit-modal-is-public-{{ $expense->id }}">
                            <span class="relative block h-6 w-11 shrink-0 rounded-full border-2 theme-border theme-bg-tertiary transition-colors peer-focus:ring-2 peer-focus:ring-orange-500/50 peer-checked:bg-orange-500 peer-checked:border-orange-500" aria-hidden="true" style="min-width: 2.75rem; min-height: 1.5rem;"></span>
                            <span class="absolute z-10 h-4 w-4 rounded-full border-2 theme-border theme-bg-secondary shadow-md transition-transform duration-200 ease-out pointer-events-none peer-checked:translate-x-5" style="left: 0.25rem; top: 0.25rem; width: 1rem; height: 1rem;" aria-hidden="true"></span>
                            <span class="text-sm theme-text-secondary whitespace-nowrap" id="expense-edit-visibility-label-{{ $expense->id }}">{{ $expense->is_public ? 'Public (anyone can see)' : 'Private (admin only)' }}</span>
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
                    <button type="button" @click="expenseEditModal = null" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
