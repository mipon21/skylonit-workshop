<x-app-layout>
    <x-slot name="title">Edit external income</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('internal-income.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-block">← Internal Income</a>
            <h1 class="text-2xl font-semibold text-white">Edit external income</h1>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-4">
                    <h3 class="text-sm font-medium text-slate-400 mb-1">Current fund balances</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between"><span class="text-slate-400">Overhead</span><span class="text-white font-medium">৳{{ number_format($overheadBalance, 0) }}</span></li>
                        <li class="flex justify-between"><span class="text-slate-400">Profit pool</span><span class="text-sky-400 font-medium">৳{{ number_format($profitBalance ?? 0, 0) }}</span></li>
                    </ul>
                </div>
            </div>
            <div>
                <form action="{{ route('internal-income.update', $income) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="title" class="block text-sm font-medium text-slate-300 mb-1">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $income->title) }}" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('title')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="amount" class="block text-sm font-medium text-slate-300 mb-1">Amount (৳)</label>
                        <input type="number" name="amount" id="amount" value="{{ old('amount', $income->amount) }}" step="0.01" min="0.01" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('amount')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="income_date" class="block text-sm font-medium text-slate-300 mb-1">Income date</label>
                        <input type="date" name="income_date" id="income_date" value="{{ old('income_date', $income->income_date->format('Y-m-d')) }}" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('income_date')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Add to fund</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-600 hover:bg-slate-800/50 cursor-pointer">
                                <input type="radio" name="fund_type" value="overhead" {{ old('fund_type', $income->fund_type) === 'overhead' ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-500">
                                <span class="text-slate-300">Overhead</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-600 hover:bg-slate-800/50 cursor-pointer">
                                <input type="radio" name="fund_type" value="profit" {{ old('fund_type', $income->fund_type) === 'profit' ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-500">
                                <span class="text-slate-300">Profit pool</span>
                            </label>
                        </div>
                        @error('fund_type')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-300 mb-1">Description (optional)</label>
                        <textarea name="description" id="description" rows="3" class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('description', $income->description) }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('internal-income.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
