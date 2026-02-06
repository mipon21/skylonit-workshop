<x-guest-portal-layout title="Contact">
    <div class="space-y-6 max-w-2xl max-md:max-w-none">
        <h1 class="text-2xl font-semibold text-white max-md:text-xl">Contact</h1>
        <p class="text-slate-400 text-sm">Submit an enquiry and we’ll get back to you soon.</p>

        <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl p-6 shadow-xl max-md:p-4">
        <form action="{{ route('guest.contact.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-slate-400 mb-1">Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-400 mb-1">Email *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-400 mb-1">Phone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                @error('phone')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="interested_project_type" class="block text-sm font-medium text-slate-400 mb-1">Interested Project Type</label>
                <select name="interested_project_type" id="interested_project_type" class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    <option value="">Select type</option>
                    @foreach(\App\Models\Project::PROJECT_TYPES as $type)
                        <option value="{{ $type }}" {{ old('interested_project_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('interested_project_type')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="message" class="block text-sm font-medium text-slate-400 mb-1">Message</label>
                <textarea name="message" id="message" rows="4" class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 resize-y">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="pt-2">
                <button type="submit" class="px-5 py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-sky-500 hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20 transition-all duration-300 max-md:w-full max-md:py-3.5">Submit</button>
            </div>
        </form>
        </div>
    </div>
</x-guest-portal-layout>
