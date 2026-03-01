<x-app-layout>
    <x-slot name="title">Edit external income</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('internal-income.index') }}" class="theme-text-secondary theme-hover-primary text-sm mb-2 inline-block">← Internal Income</a>
            <h1 class="text-2xl font-semibold theme-text-primary">Edit external income</h1>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <div class="theme-card-bg-only theme-border border rounded-2xl p-4">
                    <h3 class="text-sm font-medium theme-text-secondary mb-1">Current fund balances</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between"><span class="theme-text-secondary">Overhead</span><span class="theme-text-primary font-medium">৳{{ number_format($overheadBalance, 0) }}</span></li>
                        <li class="flex justify-between"><span class="theme-text-secondary">Profit pool</span><span class="text-orange-400 font-medium">৳{{ number_format($profitBalance ?? 0, 0) }}</span></li>
                    </ul>
                </div>
            </div>
            <div>
                <form action="{{ route('internal-income.update', $income) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="title" class="block text-sm font-medium theme-text-secondary mb-1">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $income->title) }}" required class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @error('title')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="amount" class="block text-sm font-medium theme-text-secondary mb-1">Amount (৳)</label>
                        <input type="number" name="amount" id="amount" value="{{ old('amount', $income->amount) }}" step="0.01" min="0.01" required class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @error('amount')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="income_date" class="block text-sm font-medium theme-text-secondary mb-1">Income date</label>
                        <input type="date" name="income_date" id="income_date" value="{{ old('income_date', $income->income_date->format('Y-m-d')) }}" required class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @error('income_date')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-2">Add to fund</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border theme-border hover:theme-bg-tertiary/50 cursor-pointer">
                                <input type="radio" name="fund_type" value="overhead" {{ old('fund_type', $income->fund_type) === 'overhead' ? 'checked' : '' }} class="rounded theme-border theme-bg-tertiary text-orange-500 focus:ring-orange-500">
                                <span class="theme-text-secondary">Overhead</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border theme-border hover:theme-bg-tertiary/50 cursor-pointer">
                                <input type="radio" name="fund_type" value="profit" {{ old('fund_type', $income->fund_type) === 'profit' ? 'checked' : '' }} class="rounded theme-border theme-bg-tertiary text-orange-500 focus:ring-orange-500">
                                <span class="theme-text-secondary">Profit pool</span>
                            </label>
                        </div>
                        @error('fund_type')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium theme-text-secondary mb-1">Description (optional)</label>
                        <textarea name="description" id="description" rows="3" class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">{{ old('description', $income->description) }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('internal-income.index') }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</a>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
