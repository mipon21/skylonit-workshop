<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                <div class="max-w-xl">
                    @include('profile.partials.update-logo-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                <div class="max-w-xl">
                    @include('profile.partials.update-favicon-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl shadow-inner">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
