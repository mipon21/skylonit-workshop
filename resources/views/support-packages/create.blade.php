<x-app-layout>
    <x-slot name="title">Create Support Package</x-slot>

    <div class="space-y-6 max-w-2xl">
        <div>
            <a href="{{ route('projects.show', $project) }}#support" class="theme-text-secondary theme-hover-primary text-sm">← Back to {{ $project->project_name }}</a>
            <h1 class="text-2xl font-semibold theme-text-primary mt-1">Create Support Package</h1>
            <p class="theme-text-secondary text-sm mt-0.5">Client: {{ $project->client->name ?? '—' }}</p>
        </div>

        <form action="{{ route('support-packages.store', $project) }}" method="POST" class="space-y-5">
            @csrf
            <div class="rounded-2xl theme-card-bg-only theme-border border p-5 space-y-4">
                <div>
                    <label for="package_duration" class="block text-sm font-medium theme-text-secondary mb-1">Package Duration</label>
                    <select name="package_duration" id="package_duration" required class="w-full rounded-xl theme-input-bg theme-input-border border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @foreach(\App\Models\SupportPackage::DURATIONS as $val => $label)
                        <option value="{{ $val }}" {{ old('package_duration') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('package_duration')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium theme-text-secondary mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required class="w-full rounded-xl theme-input-bg theme-input-border border theme-text-primary px-4 py-2.5 theme-input-focus">
                    @error('start_date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="amount" class="block text-sm font-medium theme-text-secondary mb-1">Amount (৳)</label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required min="0" step="0.01" placeholder="0" class="w-full rounded-xl theme-input-bg theme-input-border border theme-text-primary px-4 py-2.5 theme-input-placeholder theme-input-focus">
                    @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="send_email" id="send_email" value="1" {{ old('send_email') ? 'checked' : '' }} class="rounded theme-border theme-bg-tertiary text-orange-500 focus:ring-orange-500">
                    <label for="send_email" class="text-sm theme-text-secondary">Send email to client (when payment link is generated)</label>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium">Create Package</button>
                <a href="{{ route('projects.show', $project) }}#support" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover font-medium">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
