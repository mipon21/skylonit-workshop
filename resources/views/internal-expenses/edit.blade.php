<x-app-layout>
    <x-slot name="title">Edit internal expense</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('internal-expenses.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-block">← Internal Expenses</a>
            <h1 class="text-2xl font-semibold text-white">Edit internal expense</h1>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-4">
                    <h3 class="text-sm font-medium text-slate-400 mb-1">Current fund balances</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between"><span class="text-slate-400">Overhead</span><span class="text-white font-medium">৳{{ number_format($overheadBalance, 0) }}</span></li>
                        @foreach($investments as $inv)
                            @php $bal = $investmentBalances[$inv->id] ?? 0; @endphp
                            @if($bal > 0 || $expense->investment_id == $inv->id)
                                <li class="flex justify-between"><span class="text-slate-400">{{ $inv->investor_name }}</span><span class="text-emerald-400 font-medium">৳{{ number_format($bal, 0) }} available</span></li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
            <div>
                <form action="{{ route('internal-expenses.update', $expense) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="title" class="block text-sm font-medium text-slate-300 mb-1">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $expense->title) }}" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('title')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="amount" class="block text-sm font-medium text-slate-300 mb-1">Amount (৳)</label>
                        <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('amount')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="expense_date" class="block text-sm font-medium text-slate-300 mb-1">Expense date</label>
                        <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('expense_date')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-300 mb-1">Description (optional)</label>
                        <textarea name="description" id="description" rows="3" class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('description', $expense->description) }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Funding source</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-600 hover:bg-slate-800/50 cursor-pointer">
                                <input type="radio" name="funded_from" value="overhead" {{ old('funded_from', $expense->funded_from) === 'overhead' ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-500">
                                <span class="text-slate-300">Overhead</span>
                                <span class="text-slate-500 text-sm">(balance: ৳{{ number_format($overheadBalance, 0) }})</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-600 hover:bg-slate-800/50 cursor-pointer">
                                <input type="radio" name="funded_from" value="investment" {{ old('funded_from', $expense->funded_from) === 'investment' ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-500">
                                <span class="text-slate-300">Investor capital</span>
                            </label>
                        </div>
                        <div id="investment-select-wrap" class="mt-2 {{ old('funded_from', $expense->funded_from) === 'investment' ? '' : 'hidden' }}">
                            <label for="investment_id" class="block text-sm text-slate-400 mb-1">Select investment</label>
                            <select name="investment_id" id="investment_id" class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                <option value="">— Select —</option>
                                @foreach($investments as $inv)
                                    @php $bal = $investmentBalances[$inv->id] ?? 0; @endphp
                                    @if($bal > 0 || $expense->investment_id == $inv->id)
                                        <option value="{{ $inv->id }}" {{ old('investment_id', $expense->investment_id) == $inv->id ? 'selected' : '' }}>{{ $inv->investor_name }} (৳{{ number_format($bal, 0) }} available)</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('investment_id')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('internal-expenses.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Update expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('input[name="funded_from"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var wrap = document.getElementById('investment-select-wrap');
                wrap.classList.toggle('hidden', this.value !== 'investment');
            });
        });
    </script>
</x-app-layout>
