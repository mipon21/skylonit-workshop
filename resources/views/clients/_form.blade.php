<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Name *</label>
        <input type="text" name="name" value="{{ old('name', $client ? $client->name : '') }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $client ? $client->phone : '') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Email {{ ($client && $client->user_id) ? '' : '*' }}</label>
        <input type="email" name="email" value="{{ old('email', $client ? $client->email : '') }}" {{ ($client && $client->user_id) ? '' : 'required' }} class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        @if($client && $client->user_id)<p class="text-slate-500 text-xs mt-1">Login email for client portal.</p>@endif
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Password {{ ($client && $client->user_id) ? '(leave blank to keep current)' : '*' }}</label>
        <input type="password" name="password" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" autocomplete="new-password">
        @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        <p class="text-slate-500 text-xs mt-1">@if($client) Leave blank to keep current. @endif If "Send Email Notification" is checked at the bottom, the client will receive an email with login details (when the template is enabled).</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Confirm Password</label>
        <input type="password" name="password_confirmation" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" autocomplete="new-password">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Address</label>
        <textarea name="address" rows="2" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('address', $client ? $client->address : '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">FB Link</label>
        <input type="text" name="fb_link" value="{{ old('fb_link', $client ? $client->fb_link : '') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">KYC</label>
        <input type="text" name="kyc" value="{{ old('kyc', $client ? $client->kyc : '') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
    </div>
    <div class="pt-2 border-t border-slate-700/50">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="send_email" value="1" {{ old('send_email') ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
            <span class="text-sm font-medium text-slate-400">Send Email Notification?</span>
        </label>
        <p class="text-slate-500 text-xs mt-1">
            @if($client)
                When checked, the client receives an email with login details (if template is enabled). Use after setting a new password to send it to them.
            @else
                Default: unchecked. When checked, client receives account-created email (if template is enabled).
            @endif
        </p>
    </div>
</div>
