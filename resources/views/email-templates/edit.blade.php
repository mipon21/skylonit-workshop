<x-app-layout>
    <x-slot name="title">Edit {{ $template->name }}</x-slot>

    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('email-templates.index') }}" class="text-orange-400 hover:text-orange-300 text-sm">← Email Templates</a>
            <h1 class="text-2xl font-semibold theme-text-primary mt-2">Edit: {{ $template->name }}</h1>
            <p class="theme-text-muted font-mono text-sm mt-1">Key: {{ $template->key }}</p>
        </div>

        <div class="theme-card-bg-only theme-border border rounded-2xl p-6">
            <form action="{{ route('email-templates.update', $template) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" placeholder="e.g. {{ $placeholderExampleShort }}">
                        @error('subject')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Body (HTML)</label>
                        <textarea name="body" rows="12" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus font-mono text-sm">{{ old('body', $template->body) }}</textarea>
                        @error('body')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        <p class="theme-text-muted text-xs mt-1">Use placeholders like {{ Str::limit($placeholderExamples, 80) }}. Full list below.</p>
                    </div>
                    <div class="pt-2 border-t theme-border">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                            <span class="text-sm font-medium theme-text-secondary">Template enabled (emails can be sent when this is on and admin checks "Send Email Notification")</span>
                        </label>
                    </div>
                    <div class="rounded-xl theme-input-bg/80 border theme-border p-4">
                        <p class="text-sm font-medium theme-text-secondary mb-2">Available placeholders</p>
                        <p class="theme-text-muted text-xs font-mono leading-relaxed break-all">{{ $placeholderExamples }}</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium">Save</button>
                    <a href="{{ route('email-templates.preview', $template) }}" target="_blank" rel="noopener" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Preview (no email sent)</a>
                    <a href="{{ route('email-templates.index') }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
