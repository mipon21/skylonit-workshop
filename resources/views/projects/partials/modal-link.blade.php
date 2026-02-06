<div x-show="linkModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="linkModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="linkModal = false"></div>
        <div x-show="linkModal" x-transition class="relative w-full max-w-md bg-slate-800 border border-slate-700 rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold text-white mb-4">Add Link / APK</h2>
            <form action="{{ route('projects.links.store', $project) }}" method="POST" enctype="multipart/form-data" x-data="{ linkType: '{{ old('link_type', 'url') }}' }">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Type *</label>
                        <select name="link_type" x-model="linkType" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            <option value="url">Live URL</option>
                            <option value="apk">APK Download</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Label *</label>
                        <input type="text" name="label" value="{{ old('label') }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="e.g. Admin Panel, Staging, App v1.0">
                        @error('label')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <template x-if="linkType === 'url'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-1">URL *</label>
                                <input type="url" name="url" value="{{ old('url') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="https://...">
                                @error('url')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-1">Login username (optional)</label>
                                <input type="text" name="login_username" value="{{ old('login_username') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-1">Login password (optional)</label>
                                <input type="text" name="login_password" value="{{ old('login_password') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            </div>
                        </div>
                    </template>
                    <template x-if="linkType === 'apk'">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">APK file *</label>
                            <input type="file" name="apk_file" accept=".apk" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 file:mr-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-slate-700 file:text-slate-200 text-sm">
                            <p class="text-slate-500 text-xs mt-1">Max 100MB.</p>
                            @error('apk_file')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </template>
                    <div class="pt-2 border-t border-slate-700/50">
                        <label class="block text-sm font-medium text-slate-400 mb-1">Who can see this link?</label>
                        <select name="visibility" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            @foreach(\App\Models\ProjectLink::visibilityLabels() as $value => $label)
                                <option value="{{ $value }}" {{ old('visibility', 'client') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-slate-500 text-xs mt-1">Admin always sees all. Choose who else can see this link or APK.</p>
                    </div>
                    <div class="pt-2 border-t border-slate-700/50">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="send_email" value="1" {{ old('send_email') ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                            <span class="text-sm font-medium text-slate-400">Send Email Notification?</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="linkModal = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Add Link</button>
                </div>
            </form>
        </div>
    </div>
</div>
