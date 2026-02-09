@php
    $project = $project ?? null;
    $clients = $clients ?? \App\Models\Client::orderBy('name')->get();
    $nextProjectCode = $nextProjectCode ?? \App\Models\Project::generateNextProjectCode();
    $developers = $developers ?? collect();
    $sales = $sales ?? collect();
    $defaultDeveloperIds = $project ? $project->developers()->pluck('users.id')->all() : ($developers->count() === 1 ? [$developers->first()->id] : []);
    $defaultSalesId = $project ? $project->sales()->pluck('users.id')->first() : ($sales->count() === 1 ? $sales->first()->id : null);
    $selectedDeveloperIds = old('developer_ids', $defaultDeveloperIds);
    $selectedSalesId = old('sales_id', $defaultSalesId);
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
    {{-- Financial + Distribution: one Alpine scope for live preview. Base = Contract − Expenses. --}}
    <div class="space-y-4" x-data="{
        distributionSettingsOpen: false,
        contractAmount: {{ json_encode((float) old('contract_amount', $project?->contract_amount ?? 0)) }},
        expenseTotal: {{ json_encode((float) ($project?->expense_total ?? 0)) }},
        developerSalesMode: {{ old('developer_sales_mode', $project?->developer_sales_mode ?? false) ? 'true' : 'false' }},
        salesCommissionEnabled: {{ old('sales_commission_enabled', $project?->sales_commission_enabled ?? true) ? 'true' : 'false' }},
        salesPercentage: {{ json_encode((float) old('sales_percentage', $project?->sales_percentage ?? 25)) }},
        developerPercentage: {{ json_encode((float) old('developer_percentage', $project?->developer_percentage ?? 40)) }},
        get base() { return Math.max(0, (parseFloat(this.contractAmount) || 0) - (parseFloat(this.expenseTotal) || 0)); },
        get overheadAmount() { return this.developerSalesMode ? 0 : Math.round(this.base * 0.2 * 100) / 100; },
        get salesAmount() {
            if (this.developerSalesMode) return Math.round(this.base * 0.25 * 100) / 100;
            return this.salesCommissionEnabled ? Math.round(this.base * (parseFloat(this.salesPercentage) || 0) / 100 * 100) / 100 : 0;
        },
        get developerAmount() {
            if (this.developerSalesMode) return Math.round(this.base * 0.75 * 100) / 100;
            return Math.round(this.base * (parseFloat(this.developerPercentage) || 0) / 100 * 100) / 100;
        },
        get profitAmount() {
            if (this.developerSalesMode) return 0;
            return Math.max(0, Math.round((this.base - this.overheadAmount - this.salesAmount - this.developerAmount) * 100) / 100);
        },
        formatNum(n) { const x = Number(n); return isNaN(x) ? '0' : x.toLocaleString('en-BD', { maximumFractionDigits: 0 }); }
    }" @toggle-distribution.window="distributionSettingsOpen = !distributionSettingsOpen">
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Contract amount (৳) *</label>
            <input type="number" name="contract_amount" step="0.01" min="0" required x-model.number="contractAmount" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
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

        @if($developers->isNotEmpty())
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Assign Developers</label>
            <select name="developer_ids[]" multiple class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 min-h-[100px]">
                @foreach($developers as $dev)
                    <option value="{{ $dev->id }}" {{ in_array($dev->id, $selectedDeveloperIds) ? 'selected' : '' }}>{{ $dev->name }} ({{ $dev->email }})</option>
                @endforeach
            </select>
            <p class="text-slate-500 text-xs mt-1">Hold Ctrl/Cmd to select multiple. Assigned users receive an email when added.</p>
        </div>
        @endif
        @if($sales->isNotEmpty())
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-1">Assign Sales (one per project)</label>
            <select name="sales_id" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                <option value="">— None —</option>
                @foreach($sales as $s)
                    <option value="{{ $s->id }}" {{ (string)$selectedSalesId === (string)$s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->email }})</option>
                @endforeach
            </select>
            <p class="text-slate-500 text-xs mt-1">One sales person per project. They receive an email when assigned.</p>
        </div>
        @endif

        {{-- Distribution Settings: only visible when opened via gear beside "Add Project" or "Edit Project". --}}
        <div x-show="distributionSettingsOpen" x-cloak class="pt-4 mt-4 border-t-2 border-slate-600/70 rounded-xl bg-slate-800/40 p-4 -mx-1">
            <h3 class="text-base font-semibold text-slate-200 mb-1">Distribution Settings</h3>
            <p class="text-slate-500 text-xs mb-3">Configure how Base (Contract − Expenses) is split: Overhead, Sales, Developer, Profit.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="developer_sales_mode" value="0">
                    <input type="checkbox" name="developer_sales_mode" value="1" x-model="developerSalesMode" class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500"
                        {{ old('developer_sales_mode', $project?->developer_sales_mode ?? false) ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-slate-400">Developer–Sales (75/25)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer" :class="{ 'opacity-50 pointer-events-none': developerSalesMode }">
                    <input type="hidden" name="sales_commission_enabled" value="0">
                    <input type="checkbox" name="sales_commission_enabled" value="1" x-model="salesCommissionEnabled" class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500"
                        {{ old('sales_commission_enabled', $project?->sales_commission_enabled ?? true) ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-slate-400">Sales Commission Applicable</span>
                </label>
            </div>
            <p class="text-slate-500 text-xs mt-1">When Developer–Sales (75/25) is ON, Developer = 75% and Sales = 25% of Base; Overhead and Profit are ৳0. When OFF, Overhead is fixed at 20% of Base.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3" :class="{ 'opacity-50 pointer-events-none': developerSalesMode }">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Sales %</label>
                    <input type="number" name="sales_percentage" min="0" max="100" step="0.01" x-model.number="salesPercentage" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Developer %</label>
                    <input type="number" name="developer_percentage" min="0" max="100" step="0.01" x-model.number="developerPercentage" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>
            <p class="text-slate-500 text-xs mt-1">Sales + Developer + 20% Overhead must not exceed 100%. Unused margin flows to Profit.</p>
            @error('sales_percentage')<p class="text-red-400 text-xs">{{ $message }}</p>@enderror
            @error('developer_percentage')<p class="text-red-400 text-xs">{{ $message }}</p>@enderror
            @error('distribution')<p class="text-red-400 text-xs">{{ $message }}</p>@enderror

            <div class="mt-4 p-4 rounded-xl bg-slate-900/80 border border-slate-700/50">
                <h4 class="text-sm font-medium text-slate-400 mb-3">Preview (Base = Contract − Expenses)</h4>
                <template x-if="developerSalesMode">
                    <div class="space-y-1.5 text-sm">
                        <p class="flex justify-between"><span class="text-slate-400">Base Amount</span><span class="text-white font-medium" x-text="'৳ ' + formatNum(base)"></span></p>
                        <p class="flex justify-between"><span class="text-slate-400">Developer (75%)</span><span class="text-white font-medium" x-text="'৳ ' + formatNum(developerAmount)"></span></p>
                        <p class="flex justify-between"><span class="text-slate-400">Sales (25%)</span><span class="text-white font-medium" x-text="'৳ ' + formatNum(salesAmount)"></span></p>
                    </div>
                </template>
                <template x-if="!developerSalesMode">
                    <div class="space-y-1.5 text-sm">
                        <p class="flex justify-between"><span class="text-slate-400">Base Amount</span><span class="text-white font-medium" x-text="'৳ ' + formatNum(base)"></span></p>
                        <p class="flex justify-between"><span class="text-slate-400">Overhead (20%)</span><span class="text-white font-medium" x-text="'৳ ' + formatNum(overheadAmount)"></span></p>
                        <p class="flex justify-between"><span class="text-slate-400">Sales</span><span class="text-white font-medium" x-text="'৳ ' + formatNum(salesAmount)"></span></p>
                        <p class="flex justify-between"><span class="text-slate-400">Developer</span><span class="text-white font-medium" x-text="'৳ ' + formatNum(developerAmount)"></span></p>
                        <p class="flex justify-between"><span class="text-slate-400">Profit</span><span class="text-emerald-400 font-medium" x-text="'৳ ' + formatNum(profitAmount)"></span></p>
                    </div>
                </template>
            </div>
        </div>

        <input type="hidden" name="exclude_from_overhead_profit" :value="developerSalesMode ? '1' : '0'">
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
        <label class="block text-sm font-medium text-slate-400 mb-1">Featured image (guest dashboard)</label>
        @php
            $currentFeaturedPath = old('featured_image_path', $project->featured_image_path);
            $currentFeaturedUrl = $currentFeaturedPath ? (str_starts_with($currentFeaturedPath, 'http') ? $currentFeaturedPath : asset($currentFeaturedPath)) : null;
        @endphp
        @if($currentFeaturedUrl)
            <div class="mb-3 flex items-center gap-4">
                <img src="{{ $currentFeaturedUrl }}" alt="Current featured" class="w-24 h-24 rounded-xl object-cover border border-slate-600" onerror="this.style.display='none'">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="remove_featured_image" value="0">
                    <input type="checkbox" name="remove_featured_image" value="1" {{ old('remove_featured_image') ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-red-500 focus:ring-red-500">
                    <span class="text-sm text-slate-400">Remove current image</span>
                </label>
            </div>
        @endif
        <input type="file" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-700 file:text-slate-200 file:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        <p class="text-slate-500 text-xs mt-1">Upload an image (JPG, PNG, GIF, WebP). Used on the guest portal dashboard featured carousel. Max 2 MB.</p>
        @error('featured_image')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Tech stack (guest chips, comma-separated)</label>
        <input type="text" name="tech_stack" value="{{ old('tech_stack', $project->tech_stack) }}" placeholder="e.g. Laravel, Vue, Tailwind" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
        @error('tech_stack')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-400 mb-1">Description (guest portal project page)</label>
        <textarea name="guest_description" rows="4" placeholder="Optional. Shown only on the public guest portal project details page." class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('guest_description', $project->guest_description) }}</textarea>
        <p class="text-slate-500 text-xs mt-1">Not shown on admin or client portal—only on the guest (public) project page.</p>
        @error('guest_description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    @endif
    @if(!$project)
    <div class="pt-2 border-t border-slate-700/50">
        <label class="block text-sm font-medium text-slate-400 mb-1">Description (guest portal project page)</label>
        <textarea name="guest_description" rows="4" placeholder="Optional. Shown only on the public guest portal project details page." class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('guest_description') }}</textarea>
        <p class="text-slate-500 text-xs mt-1">Not shown on admin or client portal—only on the guest (public) project page.</p>
        @error('guest_description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="pt-2 border-t border-slate-700/50">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="send_email" value="1" {{ old('send_email', true) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
            <span class="text-sm font-medium text-slate-400">Send Email Notification?</span>
        </label>
        <p class="text-slate-500 text-xs mt-1">Default: unchecked. Notify client about the new project (if template is enabled).</p>
    </div>
    @endif
</div>
