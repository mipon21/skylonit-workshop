@php
    $project = $project ?? null;
    $clients = $clients ?? \App\Models\Client::orderBy('name')->get();
    $nextProjectCode = $nextProjectCode ?? \App\Models\Project::generateNextProjectCode();
@endphp
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Client *</label>
        <select name="client_id" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
            <option value="">Select client</option>
            @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ old('client_id', $project?->client_id ?? request('client_id')) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('client_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Project name *</label>
        <input type="text" name="project_name" value="{{ old('project_name', $project?->project_name) }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('project_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Project code</label>
        <input type="text" name="project_code" value="{{ old('project_code', $project?->project_code ?? $nextProjectCode) }}" readonly class="w-full rounded-xl bg-slate-800/80 border border-slate-600 text-slate-400 px-4 py-2.5 cursor-not-allowed" title="Not editable">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Project type</label>
        <select name="project_type" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
            <option value="">Select type</option>
            @foreach(\App\Models\Project::PROJECT_TYPES as $type)
                <option value="{{ $type }}" {{ old('project_type', $project?->project_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
        @error('project_type')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Contract amount (৳) *</label>
        <input type="number" name="contract_amount" value="{{ old('contract_amount', $project?->contract_amount) }}" step="0.01" min="0" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('contract_amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Contract date</label>
            <input type="date" name="contract_date" value="{{ old('contract_date', $project?->contract_date?->format('Y-m-d')) }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Delivery date</label>
            <input type="date" name="delivery_date" value="{{ old('delivery_date', $project?->delivery_date?->format('Y-m-d')) }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Status</label>
        <select name="status" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
            <option value="Pending" {{ old('status', $project?->status ?? 'Pending') === 'Pending' ? 'selected' : '' }}>Pending</option>
            <option value="Running" {{ old('status', $project?->status) === 'Running' ? 'selected' : '' }}>Running</option>
            <option value="Complete" {{ old('status', $project?->status) === 'Complete' ? 'selected' : '' }}>Complete</option>
            <option value="On Hold" {{ old('status', $project?->status) === 'On Hold' ? 'selected' : '' }}>On Hold</option>
        </select>
    </div>
    <div class="pt-2 border-t border-slate-700/50">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="exclude_from_overhead_profit" value="0">
            <input type="checkbox" name="exclude_from_overhead_profit" value="1" {{ old('exclude_from_overhead_profit', $project?->exclude_from_overhead_profit ?? false) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
            <span class="text-sm font-medium text-slate-400">Developer &amp; Sales only (exclude from Overhead &amp; Profit)</span>
        </label>
        <p class="text-slate-500 text-xs mt-1">When enabled, expense is deducted from the contract amount first, then 75% Developer and 25% Sales; Overhead and Profit are ৳0 and not counted in totals.</p>
    </div>
    @if($project)
    <div class="pt-2 border-t border-slate-700/50">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="is_public" value="0">
            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $project->is_public ?? false) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
            <span class="text-sm font-medium text-slate-400">Show on public (guest) portal</span>
        </label>
        <p class="text-slate-500 text-xs mt-1">When enabled, this project appears on the public showcase and guests can view its public tasks, bugs, and links.</p>
    </div>
    <div class="pt-2 border-t border-slate-700/50">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $project->is_featured ?? false) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
            <span class="text-sm font-medium text-slate-400">Featured on guest dashboard</span>
        </label>
        <p class="text-slate-500 text-xs mt-1">Show this project in the Featured Projects carousel on the public portal (only if public).</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Short description (guest featured card)</label>
        <input type="text" name="short_description" value="{{ old('short_description', $project->short_description) }}" maxlength="500" placeholder="Brief tagline for guest showcase" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('short_description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Featured image path (guest)</label>
        <input type="text" name="featured_image_path" value="{{ old('featured_image_path', $project->featured_image_path) }}" placeholder="e.g. /images/projects/example.jpg or URL" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('featured_image_path')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Tech stack (guest chips, comma-separated)</label>
        <input type="text" name="tech_stack" value="{{ old('tech_stack', $project->tech_stack) }}" placeholder="e.g. Laravel, Vue, Tailwind" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('tech_stack')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    @endif
    @if(!$project)
    <div class="pt-2 border-t border-slate-700/50">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="send_email" value="1" {{ old('send_email') ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
            <span class="text-sm font-medium text-slate-400">Send Email Notification?</span>
        </label>
        <p class="text-slate-500 text-xs mt-1">Default: unchecked. Notify client about the new project (if template is enabled).</p>
    </div>
    @endif
</div>
