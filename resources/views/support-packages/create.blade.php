<x-app-layout>
    <x-slot name="title">Create Support Package</x-slot>

    <div class="space-y-6 max-w-2xl">
        <div>
            <a href="{{ route('projects.show', $project) }}#support" class="text-slate-400 hover:text-white text-sm">← Back to {{ $project->project_name }}</a>
            <h1 class="text-2xl font-semibold text-white mt-1">Create Support Package</h1>
            <p class="text-slate-400 text-sm mt-0.5">Client: {{ $project->client->name ?? '—' }}</p>
        </div>

        <form action="{{ route('support-packages.store', $project) }}" method="POST" class="space-y-5">
            @csrf
            <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-5 space-y-4">
                <div>
                    <label for="package_duration" class="block text-sm font-medium text-slate-300 mb-1">Package Duration</label>
                    <select name="package_duration" id="package_duration" required class="w-full rounded-xl bg-slate-800/80 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @foreach(\App\Models\SupportPackage::DURATIONS as $val => $label)
                        <option value="{{ $val }}" {{ old('package_duration') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('package_duration')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-slate-300 mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required class="w-full rounded-xl bg-slate-800/80 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('start_date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="amount" class="block text-sm font-medium text-slate-300 mb-1">Amount (৳)</label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required min="0" step="0.01" class="w-full rounded-xl bg-slate-800/80 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="send_email" id="send_email" value="1" {{ old('send_email') ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-500">
                    <label for="send_email" class="text-sm text-slate-300">Send email to client (when payment link is generated)</label>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Create Package</button>
                <a href="{{ route('projects.show', $project) }}#support" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 font-medium">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
