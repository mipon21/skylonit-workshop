<div x-show="expenseModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="expenseModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="expenseModal = false"></div>
        <div x-show="expenseModal" x-transition class="relative w-full max-w-sm theme-bg-tertiary border theme-border rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold theme-text-primary mb-4">Add Expense</h2>
            <form action="{{ route('projects.expenses.store', $project) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Amount (৳) *</label>
                        <input type="number" name="amount" step="0.01" min="0" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Note</label>
                        <textarea name="note" rows="2" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus"></textarea>
                    </div>
                    <div class="pt-2 border-t theme-border">
                        <label class="flex items-center gap-2 cursor-pointer">
                            @if(($isDeveloper ?? false) || ($isClient ?? false))
                            <input type="hidden" name="send_email" value="1">
                            <p class="theme-text-muted text-xs">Email notification will be sent when relevant.</p>
                            @else
                            <input type="checkbox" name="send_email" value="1" {{ old('send_email', false) ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                            @endif
                            <span class="text-sm font-medium theme-text-secondary">Send Email Notification?</span>
                        </label>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-medium theme-text-secondary">Visibility</span>
                        <label class="js-visibility-toggle-label is-checked relative inline-flex items-center cursor-pointer gap-3 flex-shrink-0">
                            <input type="hidden" name="is_public" value="1" id="expense-modal-is-public-hidden">
                            <input type="checkbox" value="1" checked class="sr-only expense-visibility-toggle" id="expense-modal-is-public" data-hidden-id="expense-modal-is-public-hidden" data-label-id="expense-modal-visibility-label" aria-label="Public visibility">
                            <span class="visibility-toggle-track relative block h-6 w-11 shrink-0 rounded-full border-2 theme-border theme-bg-tertiary" aria-hidden="true" style="min-width: 2.75rem; min-height: 1.5rem;"></span>
                            <span class="visibility-toggle-knob absolute z-10 h-4 w-4 rounded-full border-2 theme-border theme-bg-secondary shadow-md pointer-events-none" style="left: 0.25rem; top: 0.25rem; width: 1rem; height: 1rem;" aria-hidden="true"></span>
                            <span class="text-sm theme-text-secondary whitespace-nowrap" id="expense-modal-visibility-label">Public (anyone can see)</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="expenseModal = false" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
