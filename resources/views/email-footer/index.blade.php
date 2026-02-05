<x-app-layout>
    <x-slot name="title">Email Footer</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('email-templates.index') }}" class="text-slate-400 hover:text-white text-sm">← Settings</a>
            <h1 class="text-2xl font-semibold text-white mt-1">Email Footer</h1>
            <p class="text-slate-400 text-sm mt-1">Edit the footer shown at the bottom of all notification emails (client created, payment due, etc.).</p>
        </div>

        <form action="{{ route('email-footer.update') }}" method="POST" class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6 max-w-2xl">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Email</label>
                    <input type="email" name="footer_email" value="{{ old('footer_email', $footer['email']) }}" placeholder="info@skylon-it.com" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Phone</label>
                    <input type="text" name="footer_phone" value="{{ old('footer_phone', $footer['phone']) }}" placeholder="+8801783197788" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Website URL</label>
                    <input type="url" name="footer_website" value="{{ old('footer_website', $footer['website']) }}" placeholder="https://www.skylon-it.com" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Facebook URL</label>
                    <input type="url" name="footer_facebook" value="{{ old('footer_facebook', $footer['facebook']) }}" placeholder="https://facebook.com/skylonit" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">WhatsApp number</label>
                    <input type="text" name="footer_whatsapp" value="{{ old('footer_whatsapp', $footer['whatsapp']) }}" placeholder="8801743233833" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    <p class="text-slate-500 text-xs mt-1">Digits only; link will open as https://api.whatsapp.com/send/?phone=...</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Tagline</label>
                    <input type="text" name="footer_tagline" value="{{ old('footer_tagline', $footer['tagline']) }}" placeholder="Thank you for staying with us." class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Save Footer</button>
                <a href="{{ route('email-templates.index') }}" class="ml-3 px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
