<x-guest-portal-layout title="Contact">
    <style>
    @media (max-width: 767px) {
        .guest-contact-submit { display: flex; width: 100%; justify-content: center; align-items: center; text-align: center; }
    }
    </style>
    <div class="space-y-6 max-w-2xl max-md:max-w-none">
        <h1 class="text-2xl font-semibold theme-text-primary max-md:text-xl">Contact</h1>
        <p class="theme-text-secondary text-sm">Submit an enquiry and we’ll get back to you soon.</p>

        <div class="theme-bg-tertiary/60 backdrop-blur border theme-border rounded-2xl p-6 shadow-xl max-md:p-4">
        <form action="{{ route('guest.contact.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium theme-text-secondary mb-1">Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium theme-text-secondary mb-1">Email *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium theme-text-secondary mb-1">Phone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                @error('phone')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="interested_project_type" class="block text-sm font-medium theme-text-secondary mb-1">Interested Project Type</label>
                <select name="interested_project_type" id="interested_project_type" class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                    <option value="">Select type</option>
                    @foreach(\App\Models\Project::PROJECT_TYPES as $type)
                        <option value="{{ $type }}" {{ old('interested_project_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('interested_project_type')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="message" class="block text-sm font-medium theme-text-secondary mb-1">Message</label>
                <textarea name="message" id="message" rows="4" class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus resize-y">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="pt-2">
                <button type="submit" class="guest-contact-submit inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold theme-text-primary bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-400 hover:to-amber-400 shadow-lg shadow-orange-500/30 ring-2 ring-orange-400/40 transition-all duration-300 hover:shadow-xl hover:shadow-orange-500/40 hover:-translate-y-0.5 hover:ring-orange-400/60 max-md:w-full max-md:px-5 max-md:py-3.5">
                    <svg class="w-5 h-5 max-md:w-4 max-md:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Submit
                </button>
            </div>
        </form>
        </div>
    </div>
</x-guest-portal-layout>
