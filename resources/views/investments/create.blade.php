<x-app-layout>
    <x-slot name="title">Add Investor</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('investments.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-block">← Finance → Investors</a>
            <h1 class="text-2xl font-semibold text-white">Add Investor</h1>
        </div>

        <div class="max-w-xl" x-data="{ category: '{{ old('category', 'investor') }}' }">
            <p class="text-slate-400 text-sm mb-4">Partner pool ({{ config('investor.investor_pool_percent', 95) }}% of profit) splits between shareholders and investors. Shareholders: weighted by share % (must total 100%). Investors: capped, exit at cap.</p>
            @if($shareholderTotalError ?? null)
                <p class="text-amber-400 text-sm bg-amber-500/10 border border-amber-500/30 rounded-xl px-4 py-3 mb-4">{{ $shareholderTotalError }}</p>
            @endif
            <form action="{{ route('investments.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="category" class="block text-sm font-medium text-slate-300 mb-1">Category</label>
                    <select name="category" id="category" x-model="category" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="investor" {{ old('category', 'investor') === 'investor' ? 'selected' : '' }}>Investor (temporary, capped, exits at cap)</option>
                        <option value="shareholder" {{ old('category') === 'shareholder' ? 'selected' : '' }}>Shareholder (lifetime, weighted share %, never exits)</option>
                    </select>
                    @error('category')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="investor_name" class="block text-sm font-medium text-slate-300 mb-1">Name</label>
                    <input type="text" name="investor_name" id="investor_name" value="{{ old('investor_name') }}" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('investor_name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="amount" class="block text-sm font-medium text-slate-300 mb-1">Amount (৳)</label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('amount')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="invested_at" class="block text-sm font-medium text-slate-300 mb-1">Invested at</label>
                    <input type="date" name="invested_at" id="invested_at" value="{{ old('invested_at', now()->format('Y-m-d')) }}" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('invested_at')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div x-show="category === 'shareholder'" x-transition>
                    <label for="share_percent" class="block text-sm font-medium text-slate-300 mb-1">Share percent</label>
                    <input type="number" name="share_percent" id="share_percent" value="{{ old('share_percent') }}" step="0.01" min="0.01" max="100" class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" :required="category === 'shareholder'">
                    <p class="mt-1 text-xs text-slate-500">Shareholder splits must total 100% across all shareholders.</p>
                    @error('share_percent')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div x-show="category === 'investor'" x-transition>
                    <label for="risk_level" class="block text-sm font-medium text-slate-300 mb-1">Risk level</label>
                    <select name="risk_level" id="risk_level" class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" :required="category === 'investor'">
                        @php
                            $tiers = $riskTiers ?? [
                                'low' => ['share_percent' => 25, 'cap_multiplier' => 2],
                                'medium' => ['share_percent' => 30, 'cap_multiplier' => 2.5],
                                'high' => ['share_percent' => 40, 'cap_multiplier' => 3],
                            ];
                        @endphp
                        @foreach($tiers as $key => $tier)
                            <option value="{{ $key }}" {{ old('risk_level', 'low') === $key ? 'selected' : '' }}>{{ ucfirst($key) }} ({{ $tier['share_percent'] ?? 25 }}% of investor pool, {{ $tier['cap_multiplier'] ?? 2 }}× cap)</option>
                        @endforeach
                    </select>
                    @error('risk_level')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium text-slate-300 mb-1">Notes (optional)</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('investments.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium" x-text="category === 'shareholder' ? 'Add Shareholder' : 'Add Investor'"></button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
