<x-app-layout>
    <x-slot name="title">Preview: {{ $template->name }}</x-slot>

    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('email-templates.edit', $template) }}" class="text-orange-400 hover:text-orange-300 text-sm">← Back to edit</a>
            <h1 class="text-2xl font-semibold theme-text-primary mt-2">Preview: {{ $template->name }}</h1>
            <p class="theme-text-muted text-sm mt-1">Sample data only. No email is sent.</p>
        </div>

        <div class="theme-card-bg-only theme-border border rounded-2xl p-6 space-y-6">
            <div>
                <p class="text-sm font-medium theme-text-secondary mb-1">Subject</p>
                <p class="theme-text-primary font-mono text-sm theme-input-bg rounded-lg px-4 py-2">{{ $subject }}</p>
            </div>
            <div>
                <p class="text-sm font-medium theme-text-secondary mb-1">Preview (as recipients see it)</p>
                <div class="rounded-xl overflow-hidden border theme-border/50 bg-white">
                    <iframe
                        srcdoc="{!! e($fullEmailHtml ?? $body) !!}"
                        class="w-full min-h-[400px] border-0"
                        style="height: 500px;"
                        title="Email preview"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
