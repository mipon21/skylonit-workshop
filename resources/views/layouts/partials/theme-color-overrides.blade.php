@php
$primary = \App\Models\Setting::get('theme_primary_color');
$secondary = \App\Models\Setting::get('theme_secondary_color');
$buttonBg = \App\Models\Setting::get('theme_button_bg');
$buttonHoverBg = \App\Models\Setting::get('theme_button_hover_bg');
$buttonText = \App\Models\Setting::get('theme_button_text');
$sidebarActiveBg = \App\Models\Setting::get('theme_sidebar_active_bg');
$sidebarActiveText = \App\Models\Setting::get('theme_sidebar_active_text');
$hasOverrides = !empty($primary) || !empty($secondary) || !empty($buttonBg) || !empty($buttonHoverBg) || !empty($buttonText) || !empty($sidebarActiveBg) || !empty($sidebarActiveText);
if (!$hasOverrides) {
    $overrideCss = null;
} else {
    $hexToRgba = function ($hex, $alpha = 1) {
        if (!$hex || !preg_match('/^#?([0-9A-Fa-f]{6})$/', $hex, $m)) {
            return null;
        }
        $r = hexdec(substr($m[1], 0, 2));
        $g = hexdec(substr($m[1], 2, 2));
        $b = hexdec(substr($m[1], 4, 2));
        return "rgba({$r},{$g},{$b},{$alpha})";
    };
    $vars = [];
    if ($primary) {
        $vars[] = '--status-info: ' . $primary . ';';
        $vars[] = '--status-info-bg: ' . ($hexToRgba($primary, 0.2) ?? $primary) . ';';
        $vars[] = '--input-focus-ring: ' . ($hexToRgba($primary, 0.5) ?? $primary) . ';';
    }
    if ($sidebarActiveText) {
        $vars[] = '--sidebar-active-text: ' . $sidebarActiveText . ';';
    } elseif ($primary) {
        $vars[] = '--sidebar-active-text: ' . $primary . ';';
    }
    if ($sidebarActiveBg) {
        $vars[] = '--sidebar-active-bg: ' . ($hexToRgba($sidebarActiveBg, 0.2) ?? $sidebarActiveBg) . ';';
    } elseif ($secondary) {
        $vars[] = '--sidebar-active-bg: ' . ($hexToRgba($secondary, 0.2) ?? $secondary) . ';';
    }
    if ($buttonBg) {
        $vars[] = '--button-primary-bg: ' . $buttonBg . ';';
    }
    if ($buttonHoverBg) {
        $vars[] = '--button-primary-hover-bg: ' . $buttonHoverBg . ';';
    }
    if ($buttonText) {
        $vars[] = '--button-primary-text: ' . $buttonText . ';';
    }
    $overrideCss = implode("\n    ", $vars);
}
@endphp
@if(!empty($overrideCss))
{{-- Admin-configured theme color overrides (apply to both themes) --}}
<style>
:root,
[data-theme="dark"],
[data-theme="light"] {
    {!! $overrideCss !!}
}
</style>
@endif
