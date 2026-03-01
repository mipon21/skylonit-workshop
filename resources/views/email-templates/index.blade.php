<x-app-layout>
    <x-slot name="title">Email Templates</x-slot>

    <div class="max-w-4xl">
        <h1 class="text-2xl font-semibold theme-text-primary mb-2">Email Templates</h1>
        <p class="theme-text-secondary text-sm mb-4">Edit subject and body for each notification. Emails are only sent when the template is enabled and the action has "Send Email Notification" checked.</p>
        <div class="mb-6 rounded-xl theme-bg-tertiary/80 border theme-border p-4 text-sm theme-text-secondary">
            <span class="font-medium theme-text-secondary">Verify mail without sending to your inbox:</span> Set <code class="theme-input-bg px-1 rounded">MAIL_MAILER=log</code> in .env, then run <code class="theme-input-bg px-1 rounded">php artisan mail:test</code>. The test message is written to <code class="theme-input-bg px-1 rounded">storage/logs/laravel.log</code> and no email is sent.
        </div>

        <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden max-md:overflow-x-auto">
            <div class="overflow-x-auto">
            <table class="w-full text-left max-md:min-w-[500px]">
                <thead class="theme-input-bg/80 border-b theme-border">
                    <tr>
                        <th class="px-4 py-3 text-sm font-medium theme-text-secondary">Name</th>
                        <th class="px-4 py-3 text-sm font-medium theme-text-secondary">Key</th>
                        <th class="px-4 py-3 text-sm font-medium theme-text-secondary">Status</th>
                        <th class="px-4 py-3 text-sm font-medium theme-text-secondary text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @foreach($templates as $template)
                    <tr class="hover:theme-bg-tertiary/40">
                        <td class="px-4 py-3 theme-text-secondary">{{ $template->name }}</td>
                        <td class="px-4 py-3 theme-text-muted font-mono text-sm">{{ $template->key }}</td>
                        <td class="px-4 py-3">
                            @if($template->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/20 text-emerald-400">Enabled</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-500/20 theme-text-secondary">Disabled</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('email-templates.edit', $template) }}" class="text-orange-400 hover:text-orange-300 text-sm font-medium">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
</x-app-layout>
