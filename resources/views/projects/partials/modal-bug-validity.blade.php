@foreach($project->bugs as $bug)
<div x-show="bugValidityModal === {{ $bug->id }}" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="bugValidityModal === {{ $bug->id }}" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="bugValidityModal = null"></div>
        <div x-show="bugValidityModal === {{ $bug->id }}" x-transition class="relative w-full max-w-md theme-bg-tertiary border theme-border rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold theme-text-primary mb-4">Bug Validity</h2>
            <form action="{{ route('projects.bugs.update', [$project, $bug]) }}" method="POST" enctype="multipart/form-data" x-data="{ isValid: {{ old('is_valid', $bug->is_valid ?? true) ? 'true' : 'false' }} }">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div class="pt-1">
                        <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border theme-border cursor-pointer select-none">
                            <input type="hidden" name="is_valid" value="0">
                            <input type="checkbox" name="is_valid" value="1" x-model="isValid" class="rounded theme-border theme-input-bg text-emerald-500 focus:ring-emerald-500">
                            <span class="text-sm font-medium theme-text-secondary">Valid bug</span>
                        </label>
                        <p class="theme-text-muted text-xs mt-1">If turned off, this bug will be marked invalid and client will be notified.</p>
                    </div>

                    <div x-show="!isValid" x-transition>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Invalid Note (Optional)</label>
                        <textarea name="invalid_note" rows="3" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" placeholder="Reason why this bug report is invalid">{{ old('invalid_note', $bug->invalid_note) }}</textarea>
                    </div>

                    <div x-show="!isValid" x-transition>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Invalid Attachment (Optional)</label>
                        @if($bug->invalid_attachment_path)
                            <p class="theme-text-muted text-xs mb-1">Current invalid attachment exists. Upload a new file to replace, or check below to remove.</p>
                            <label class="inline-flex items-center gap-2 theme-text-secondary text-sm">
                                <input type="hidden" name="remove_invalid_attachment" value="0">
                                <input type="checkbox" name="remove_invalid_attachment" value="1" class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                                Remove invalid attachment
                            </label>
                        @endif
                        <input type="file" name="invalid_attachment" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip,.txt" class="mt-2 w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 file:mr-3 file:py-1.5 file:rounded-lg file:border-0 file:theme-bg-tertiary file:theme-text-secondary text-sm">
                        <p class="theme-text-muted text-xs mt-1">Optional. Max 10MB.</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="bugValidityModal = null" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 theme-text-primary font-medium">Save Validity</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
