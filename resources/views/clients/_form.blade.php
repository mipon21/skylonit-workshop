<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">Name *</label>
        <input type="text" name="name" value="{{ old('name', $client ? $client->name : '') }}" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
        @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $client ? $client->phone : '') }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">Email {{ ($client && $client->user_id) ? '' : '*' }}</label>
        <input type="email" name="email" value="{{ old('email', $client ? $client->email : '') }}" {{ ($client && $client->user_id) ? '' : 'required' }} class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
        @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        @if($client && $client->user_id)<p class="theme-text-muted text-xs mt-1">Login email for client portal.</p>@endif
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">Password {{ ($client && $client->user_id) ? '(leave blank to keep current)' : '* (default: jBQw9xRg)' }}</label>
        <input type="password" name="password" value="{{ old('password', ($client && $client->user_id) ? '' : 'jBQw9xRg') }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" autocomplete="new-password">
        @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        <p class="theme-text-muted text-xs mt-1">@if($client) Leave blank to keep current. @endif If "Send Email Notification" is checked at the bottom, the client will receive an email with login details (when the template is enabled).</p>
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">Confirm Password {{ ($client && $client->user_id) ? '' : '(default: jBQw9xRg)' }}</label>
        <input type="password" name="password_confirmation" value="{{ old('password_confirmation', ($client && $client->user_id) ? '' : 'jBQw9xRg') }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" autocomplete="new-password">
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">Address</label>
        <textarea name="address" rows="2" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">{{ old('address', $client ? $client->address : '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">FB Link</label>
        <input type="text" name="fb_link" value="{{ old('fb_link', $client ? $client->fb_link : '') }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">WhatsApp number (optional)</label>
        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $client ? $client->whatsapp_number : '') }}" placeholder="e.g. 8801712000001" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
        <p class="theme-text-muted text-xs mt-1">Stored as number only; link opens as https://api.whatsapp.com/send/?phone=...</p>
    </div>
    <div>
        <label class="block text-sm font-medium theme-text-secondary mb-1">KYC</label>
        <input type="text" name="kyc" value="{{ old('kyc', $client ? $client->kyc : '') }}" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
    </div>
    <div class="pt-2 border-t theme-border">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="send_email" value="1" {{ old('send_email', true) ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
            <span class="text-sm font-medium theme-text-secondary">Send Email Notification?</span>
        </label>
        <p class="theme-text-muted text-xs mt-1">
            @if($client)
                When checked, the client receives an email with login details (if template is enabled). Use after setting a new password to send it to them.
            @else
                Default: unchecked. When checked, client receives account-created email (if template is enabled).
            @endif
        </p>
    </div>
</div>
