@php
    $user = $user ?? null;
@endphp
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">Name *</label>
        <input type="text" name="name" value="{{ old('name', $user?->name) }}" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
        @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">Email *</label>
        <input type="email" name="email" value="{{ old('email', $user?->email) }}" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
        @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">Password {{ $user ? '(leave blank to keep current)' : '* (default: jBQw9xRg)' }}</label>
        <input type="password" name="password" value="{{ old('password', $user ? '' : 'jBQw9xRg') }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" autocomplete="new-password">
        @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        <p class="theme-text-muted text-xs mt-1">If "Send Email Notification" is checked, they will receive an email with login details (when the template is enabled).</p>
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">Confirm Password {{ $user ? '' : '(default: jBQw9xRg)' }}</label>
        <input type="password" name="password_confirmation" value="{{ old('password_confirmation', $user ? '' : 'jBQw9xRg') }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" autocomplete="new-password">
    </div>
    <div class="pt-2 border-t theme-border">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="send_email" value="1" {{ old('send_email', false) ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
            <span class="text-sm font-medium theme-text-secondary">Send Email Notification (account creation / login info)</span>
        </label>
        <p class="theme-text-muted text-xs mt-1">Default: off. When checked, they receive an email with login URL and password (if template is enabled).</p>
    </div>
</div>
