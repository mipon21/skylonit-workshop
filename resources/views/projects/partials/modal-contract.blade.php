<div x-show="contractModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="contractModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="contractModal = false"></div>
        <div x-show="contractModal" x-transition class="relative w-full max-w-sm bg-slate-800 border border-slate-700 rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold text-white mb-4">Upload Contract</h2>
            <form action="{{ route('projects.contracts.store', $project) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">File (PDF preferred, or DOC/DOCX) *</label>
                        <input type="file" name="file" required accept=".pdf,.doc,.docx" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 file:mr-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-slate-700 file:text-slate-200 text-sm">
                        @error('file')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="pt-2 border-t border-slate-700/50">
                        <label class="flex items-center gap-2 cursor-pointer">
                            @if(($isDeveloper ?? false) || ($isClient ?? false))
            <input type="hidden" name="send_email" value="1">
            <p class="text-slate-500 text-xs">Email notification will be sent when relevant.</p>
            @else
            <input type="checkbox" name="send_email" value="1" {{ old('send_email', false) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
            @endif
                            <span class="text-sm font-medium text-slate-400">Send email to client with contract link?</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="contractModal = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
