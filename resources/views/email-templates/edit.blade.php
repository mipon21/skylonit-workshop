<x-app-layout>
    <x-slot name="title">Edit {{ $template->name }}</x-slot>

    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('email-templates.index') }}" class="text-sky-400 hover:text-sky-300 text-sm">← Email Templates</a>
            <h1 class="text-2xl font-semibold text-white mt-2">Edit: {{ $template->name }}</h1>
            <p class="text-slate-500 font-mono text-sm mt-1">Key: {{ $template->key }}</p>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6">
            <form action="{{ route('email-templates.update', $template) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="e.g. {{ $placeholderExampleShort }}">
                        @error('subject')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Body (HTML)</label>
                        <textarea name="body" rows="12" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 font-mono text-sm">{{ old('body', $template->body) }}</textarea>
                        @error('body')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        <p class="text-slate-500 text-xs mt-1">Use placeholders like {{ Str::limit($placeholderExamples, 80) }}. Full list below.</p>
                    </div>
                    <div class="pt-2 border-t border-slate-700/50">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                            <span class="text-sm font-medium text-slate-400">Template enabled (emails can be sent when this is on and admin checks "Send Email Notification")</span>
                        </label>
                    </div>
                    <div class="rounded-xl bg-slate-900/80 border border-slate-700/50 p-4">
                        <p class="text-sm font-medium text-slate-400 mb-2">Available placeholders</p>
                        <p class="text-slate-500 text-xs font-mono leading-relaxed break-all">{{ $placeholderExamples }}</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Save</button>
                    <a href="{{ route('email-templates.preview', $template) }}" target="_blank" rel="noopener" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Preview (no email sent)</a>
                    <a href="{{ route('email-templates.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
