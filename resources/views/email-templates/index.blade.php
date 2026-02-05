<x-app-layout>
    <x-slot name="title">Email Templates</x-slot>

    <div class="max-w-4xl">
        <h1 class="text-2xl font-semibold text-white mb-2">Email Templates</h1>
        <p class="text-slate-400 text-sm mb-4">Edit subject and body for each notification. Emails are only sent when the template is enabled and the action has "Send Email Notification" checked.</p>
        <div class="mb-6 rounded-xl bg-slate-800/80 border border-slate-700/50 p-4 text-sm text-slate-400">
            <span class="font-medium text-slate-300">Verify mail without sending to your inbox:</span> Set <code class="bg-slate-900 px-1 rounded">MAIL_MAILER=log</code> in .env, then run <code class="bg-slate-900 px-1 rounded">php artisan mail:test</code>. The test message is written to <code class="bg-slate-900 px-1 rounded">storage/logs/laravel.log</code> and no email is sent.
        </div>

        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-900/80 border-b border-slate-700/50">
                    <tr>
                        <th class="px-4 py-3 text-sm font-medium text-slate-400">Name</th>
                        <th class="px-4 py-3 text-sm font-medium text-slate-400">Key</th>
                        <th class="px-4 py-3 text-sm font-medium text-slate-400">Status</th>
                        <th class="px-4 py-3 text-sm font-medium text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @foreach($templates as $template)
                    <tr class="hover:bg-slate-800/40">
                        <td class="px-4 py-3 text-slate-200">{{ $template->name }}</td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-sm">{{ $template->key }}</td>
                        <td class="px-4 py-3">
                            @if($template->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/20 text-emerald-400">Enabled</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-500/20 text-slate-400">Disabled</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('email-templates.edit', $template) }}" class="text-sky-400 hover:text-sky-300 text-sm font-medium">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
