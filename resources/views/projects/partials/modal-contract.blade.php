<div x-show="contractModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="contractModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="contractModal = false"></div>
        <div x-show="contractModal" x-transition class="relative w-full max-w-sm theme-bg-tertiary border theme-border rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold theme-text-primary mb-4">Upload Contract</h2>
            <form action="{{ route('projects.contracts.store', $project) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">File (PDF preferred, or DOC/DOCX) *</label>
                        <input type="file" name="file" required accept=".pdf,.doc,.docx" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 file:mr-3 file:py-1.5 file:rounded-lg file:border-0 file:theme-bg-tertiary file:theme-text-secondary text-sm">
                        @error('file')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="pt-2 border-t theme-border">
                        <label class="flex items-center gap-2 cursor-pointer">
                            @if(($isDeveloper ?? false) || ($isClient ?? false))
            <input type="hidden" name="send_email" value="1">
            <p class="theme-text-muted text-xs">Email notification will be sent when relevant.</p>
            @else
            <input type="checkbox" name="send_email" value="1" {{ old('send_email', false) ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
            @endif
                            <span class="text-sm font-medium theme-text-secondary">Send email to client with contract link?</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="contractModal = false" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
