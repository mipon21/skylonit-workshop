<x-app-layout>
    <x-slot name="title">Preview: {{ $template->name }}</x-slot>

    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('email-templates.edit', $template) }}" class="text-sky-400 hover:text-sky-300 text-sm">← Back to edit</a>
            <h1 class="text-2xl font-semibold text-white mt-2">Preview: {{ $template->name }}</h1>
            <p class="text-slate-500 text-sm mt-1">Sample data only. No email is sent.</p>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6 space-y-6">
            <div>
                <p class="text-sm font-medium text-slate-400 mb-1">Subject</p>
                <p class="text-white font-mono text-sm bg-slate-900 rounded-lg px-4 py-2">{{ $subject }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-400 mb-1">Body (HTML)</p>
                <div class="bg-white text-slate-800 rounded-xl p-6 prose prose-slate max-w-none">
                    {!! $body !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
