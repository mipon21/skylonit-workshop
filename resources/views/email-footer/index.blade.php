<x-app-layout>
    <x-slot name="title">Email Footer</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('email-templates.index') }}" class="theme-text-secondary theme-hover-primary text-sm">← Settings</a>
            <h1 class="text-2xl font-semibold theme-text-primary mt-1">Email Footer</h1>
            <p class="theme-text-secondary text-sm mt-1">Edit the footer shown at the bottom of all notification emails (client created, payment due, etc.).</p>
        </div>

        <form action="{{ route('email-footer.update') }}" method="POST" class="theme-card-bg-only theme-border border rounded-2xl p-6 max-w-2xl">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Email</label>
                    <input type="email" name="footer_email" value="{{ old('footer_email', $footer['email']) }}" placeholder="info@skylon-it.com" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                </div>
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Phone</label>
                    <input type="text" name="footer_phone" value="{{ old('footer_phone', $footer['phone']) }}" placeholder="+8801783197788" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                </div>
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Website URL</label>
                    <input type="url" name="footer_website" value="{{ old('footer_website', $footer['website']) }}" placeholder="https://www.skylon-it.com" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                </div>
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Facebook URL</label>
                    <input type="url" name="footer_facebook" value="{{ old('footer_facebook', $footer['facebook']) }}" placeholder="https://facebook.com/skylonit" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                </div>
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">WhatsApp number</label>
                    <input type="text" name="footer_whatsapp" value="{{ old('footer_whatsapp', $footer['whatsapp']) }}" placeholder="8801743233833" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                    <p class="theme-text-muted text-xs mt-1">Digits only; link will open as https://api.whatsapp.com/send/?phone=...</p>
                </div>
                <div>
                    <label class="block text-sm font-medium theme-text-secondary mb-1">Tagline</label>
                    <input type="text" name="footer_tagline" value="{{ old('footer_tagline', $footer['tagline']) }}" placeholder="Thank you for staying with us." class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium">Save Footer</button>
                <a href="{{ route('email-templates.index') }}" class="ml-3 px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
