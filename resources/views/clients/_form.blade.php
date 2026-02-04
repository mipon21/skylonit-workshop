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
        <label class="block text-sm font-medium text-slate-400 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $client ?? null ? $client->email : '') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
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
