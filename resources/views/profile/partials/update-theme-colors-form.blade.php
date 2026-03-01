{{-- Admin only: theme color overrides (primary, secondary, button, sidebar active). --}}
<div class="p-4 sm:p-8 theme-card-bg theme-border border rounded-2xl shadow-inner">
    <div class="max-w-2xl">
        <h3 class="text-lg font-semibold theme-text-primary mb-1">Theme colors</h3>
        <p class="theme-text-muted text-sm mb-4">Customize primary, secondary, and button colors for the entire app. Leave blank to use defaults. Applies to all users.</p>

        @if(session('status') === 'theme-colors-updated')
            <p class="mb-4 text-sm theme-status-success">Theme colors updated. Refresh the page if you don’t see changes.</p>
        @endif
        @if(session('status') === 'theme-colors-reset')
            <p class="mb-4 text-sm theme-status-success">Theme colors reset to defaults.</p>
        @endif

        <form action="{{ route('profile.theme-colors.update') }}" method="POST" class="space-y-4" x-data="themeColorForm()">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Primary color</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="color_primary" x-model="primaryHex" class="h-10 w-14 rounded border theme-border cursor-pointer bg-transparent">
                        <input type="text" name="primary" value="{{ $themeColors['primary'] ?? '' }}" x-model="primaryHex" placeholder="#EF8121" class="flex-1 rounded-xl theme-input-bg theme-input-border border theme-text-primary theme-input-placeholder px-3 py-2 text-sm theme-input-focus" maxlength="7">
                    </div>
                    <p class="theme-text-muted text-xs mt-1">Sidebar active text, links, status info</p>
                </div>
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Secondary color</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="color_secondary" x-model="secondaryHex" class="h-10 w-14 rounded border theme-border cursor-pointer bg-transparent">
                        <input type="text" name="secondary" value="{{ $themeColors['secondary'] ?? '' }}" x-model="secondaryHex" placeholder="#EF8121" class="flex-1 rounded-xl theme-input-bg theme-input-border border theme-text-primary theme-input-placeholder px-3 py-2 text-sm theme-input-focus" maxlength="7">
                    </div>
                    <p class="theme-text-muted text-xs mt-1">Sidebar active background tint</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Button background</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="color_button_bg" x-model="buttonBgHex" class="h-10 w-14 rounded border theme-border cursor-pointer bg-transparent">
                        <input type="text" name="button_bg" value="{{ $themeColors['button_bg'] ?? '' }}" x-model="buttonBgHex" placeholder="#EF8121" class="flex-1 rounded-xl theme-input-bg theme-input-border border theme-text-primary theme-input-placeholder px-3 py-2 text-sm theme-input-focus" maxlength="7">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Button hover background</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="color_button_hover_bg" x-model="buttonHoverBgHex" class="h-10 w-14 rounded border theme-border cursor-pointer bg-transparent">
                        <input type="text" name="button_hover_bg" value="{{ $themeColors['button_hover_bg'] ?? '' }}" x-model="buttonHoverBgHex" placeholder="#EF8121" class="flex-1 rounded-xl theme-input-bg theme-input-border border theme-text-primary theme-input-placeholder px-3 py-2 text-sm theme-input-focus" maxlength="7">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Button text color</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="color_button_text" x-model="buttonTextHex" class="h-10 w-14 rounded border theme-border cursor-pointer bg-transparent">
                        <input type="text" name="button_text" value="{{ $themeColors['button_text'] ?? '' }}" x-model="buttonTextHex" placeholder="#ffffff" class="flex-1 rounded-xl theme-input-bg theme-input-border border theme-text-primary theme-input-placeholder px-3 py-2 text-sm theme-input-focus" maxlength="7">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Sidebar active background</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="color_sidebar_active_bg" x-model="sidebarActiveBgHex" class="h-10 w-14 rounded border theme-border cursor-pointer bg-transparent">
                        <input type="text" name="sidebar_active_bg" value="{{ $themeColors['sidebar_active_bg'] ?? '' }}" x-model="sidebarActiveBgHex" placeholder="Default tint" class="flex-1 rounded-xl theme-input-bg theme-input-border border theme-text-primary theme-input-placeholder px-3 py-2 text-sm theme-input-focus" maxlength="7">
                    </div>
                    <p class="theme-text-muted text-xs mt-1">Hex only; opacity applied automatically</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium theme-text-secondary mb-1">Sidebar active text</label>
                <div class="flex gap-2 items-center max-w-xs">
                    <input type="color" id="color_sidebar_active_text" x-model="sidebarActiveTextHex" class="h-10 w-14 rounded border theme-border cursor-pointer bg-transparent">
                    <input type="text" name="sidebar_active_text" value="{{ $themeColors['sidebar_active_text'] ?? '' }}" x-model="sidebarActiveTextHex" placeholder="Default" class="flex-1 rounded-xl theme-input-bg theme-input-border border theme-text-primary theme-input-placeholder px-3 py-2 text-sm theme-input-focus" maxlength="7">
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium text-sm">Save theme colors</button>
                <form action="{{ route('profile.theme-colors.reset') }}" method="POST" class="inline" onsubmit="return confirm('Clear all custom theme colors and use defaults?');">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-xl theme-btn-secondary text-sm font-medium">Reset to defaults</button>
                </form>
            </div>
        </form>

        <p class="theme-text-muted text-xs mt-3">Color pickers and text fields are synced. Leave fields blank and save to clear. Reset clears all custom colors.</p>
    </div>
</div>

<script>
function themeColorForm() {
    const initial = @json($themeColors ?? []);
    const def = (v, d) => (v && /^#[0-9A-Fa-f]{6}$/.test(v)) ? v : (d || '');
    return {
        primaryHex: def(initial.primary, '#EF8121'),
        secondaryHex: def(initial.secondary, '#EF8121'),
        buttonBgHex: def(initial.button_bg, '#EF8121'),
        buttonHoverBgHex: def(initial.button_hover_bg, '#EF8121'),
        buttonTextHex: def(initial.button_text, '#ffffff'),
        sidebarActiveBgHex: initial.sidebar_active_bg || '',
        sidebarActiveTextHex: initial.sidebar_active_text || ''
    };
}
</script>
