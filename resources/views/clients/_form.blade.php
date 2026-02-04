<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Name *</label>
        <input type="text" name="name" value="{{ old('name', $client ?? null ? $client->name : '') }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $client ?? null ? $client->phone : '') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Email {{ isset($client) && $client->user_id ? '' : '*' }}</label>
        <input type="email" name="email" value="{{ old('email', $client ?? null ? $client->email : '') }}" {{ (!isset($client) || !$client->user_id) ? 'required' : '' }} class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        @if(isset($client) && $client->user_id)<p class="text-slate-500 text-xs mt-1">Login email for client portal.</p>@endif
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Password {{ (!isset($client) || !$client->user_id) ? '*' : '(leave blank to keep current)' }}</label>
        <input type="password" name="password" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" autocomplete="new-password">
        @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        <p class="text-slate-500 text-xs mt-1">Send email + password to client manually. No automatic email.</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Confirm Password</label>
        <input type="password" name="password_confirmation" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" autocomplete="new-password">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Address</label>
        <textarea name="address" rows="2" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('address', $client ?? null ? $client->address : '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">FB Link</label>
        <input type="text" name="fb_link" value="{{ old('fb_link', $client ?? null ? $client->fb_link : '') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">KYC</label>
        <input type="text" name="kyc" value="{{ old('kyc', $client ?? null ? $client->kyc : '') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
    </div>
</div>
