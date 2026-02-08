<x-guest-portal-layout title="Dashboard">
    <style>
.guest-hero-subtext { margin-bottom: 36px; }
@media (max-width: 767px) { .guest-hero-subtext { margin-bottom: 32px; } }
@media (max-width: 767px) {
  .guest-hero-view-projects { background: #08B1DA !important; color: #fff !important; border-color: #08B1DA !important; }
  .guest-hero-view-projects:hover { background: #07a0c7 !important; border-color: #07a0c7 !important; }
}
@media (max-width: 767px) {
  .guest-hero-buttons .guest-btn-mobile { display: flex; width: 100%; justify-content: center; align-items: center; text-align: center; }
}
@media (max-width: 767px) {
  .guest-hot-offer-cta { display: flex !important; justify-content: center; align-items: center; text-align: center; }
}
/* Hot Offer description: readable Bengali/English, lists and highlights */
.hot-offer-description { font-size: 0.875rem; line-height: 1.6; color: rgb(148 163 184); text-align: left; max-height: 420px; overflow-y: auto; padding-right: 4px; }
.hot-offer-description::-webkit-scrollbar { width: 6px; }
.hot-offer-description::-webkit-scrollbar-track { background: rgba(51,65,85,0.3); border-radius: 9999px; }
.hot-offer-description::-webkit-scrollbar-thumb { background: rgba(100,116,139,0.5); border-radius: 9999px; }
.hot-offer-description p.hot-offer-paragraph { margin: 0.5rem 0; }
.hot-offer-description p.hot-offer-paragraph:first-child { font-size: 1rem; font-weight: 600; color: rgb(226 232 240); }
.hot-offer-description p.hot-offer-highlight { margin: 0.5rem 0; padding: 0.35rem 0; border-left: 3px solid rgba(6,182,212,0.6); padding-left: 0.75rem; color: rgb(203 213 225); }
.hot-offer-description ul.hot-offer-list { list-style: none; padding-left: 0; margin: 0.5rem 0 1rem 0; }
.hot-offer-description ul.hot-offer-list li { position: relative; padding-left: 1.25rem; margin-bottom: 0.35rem; }
.hot-offer-description ul.hot-offer-list li::before { content: "•"; position: absolute; left: 0; color: rgb(6 182 212); font-weight: bold; }
</style>
    {{-- Hero: extra bottom padding so dashboard cards never overlap the buttons --}}
    <section class="relative z-10 rounded-2xl overflow-hidden mb-6 max-md:mb-6">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-cyan-900/30 to-sky-900/40"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_0%,rgba(6,182,212,0.25),transparent)]"></div>
        <div class="relative px-6 pt-14 pb-10 max-md:pt-10 max-md:pb-8 max-md:px-4 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight mb-3 max-md:text-2xl">Build Your Next Digital Product With Skylon-IT</h1>
            <p class="guest-hero-subtext text-slate-300 text-lg max-w-xl mx-auto max-md:text-base">We turn ideas into polished apps and websites. From concept to launch—quality code, clear communication, on time.</p>
            <div class="flex flex-wrap items-center justify-center gap-4 max-md:flex-col max-md:gap-2 max-md:pb-2 guest-hero-buttons max-md:w-full">
                <a href="{{ route('guest.contact') }}" class="guest-btn-mobile inline-flex items-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-sky-500 hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/25 transition-all duration-300 hover:shadow-cyan-500/40 hover:-translate-y-0.5 max-md:px-3 max-md:py-2 max-md:text-sm max-md:gap-1.5">
                    <svg class="w-5 h-5 max-md:w-4 max-md:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Start Project
                </a>
                <a href="{{ route('guest.projects.index') }}" class="guest-btn-mobile guest-hero-view-projects inline-flex items-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-slate-200 bg-slate-800/80 border border-slate-600/80 hover:bg-slate-700/80 hover:border-slate-500 transition-all duration-300 max-md:px-3 max-md:py-2 max-md:text-sm max-md:gap-1.5 max-md:border-slate-500/80">
                    <svg class="w-5 h-5 max-md:w-4 max-md:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    View Projects
                </a>
            </div>
        </div>
    </section>

    {{-- Stats (animated counters) – clear gap below hero so buttons stay visible --}}
    <section class="mb-12 max-md:mb-10 mt-2" x-data="guestStats()">
        <div class="grid grid-cols-2 gap-4 max-md:gap-3">
            <a href="{{ route('guest.projects.index') }}" class="block bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl hover:border-slate-600/50 transition-all duration-300 hover:-translate-y-0.5 max-md:p-4 cursor-pointer">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Public Projects</span>
                </div>
                <p class="text-2xl font-bold text-white tabular-nums" x-text="totalPublicProjects">0</p>
                <p class="text-slate-500 text-xs mt-0.5">Showcase</p>
            </a>
            <a href="{{ route('guest.projects.index', ['status' => 'running']) }}" class="block bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl hover:border-slate-600/50 transition-all duration-300 hover:-translate-y-0.5 max-md:p-4 cursor-pointer">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Running</span>
                </div>
                <p class="text-2xl font-bold text-white tabular-nums" x-text="runningPublicProjects">0</p>
                <p class="text-slate-500 text-xs mt-0.5">Pending + Running</p>
            </a>
        </div>
    </section>

    {{-- Featured Projects: horizontal carousel, equal card width and spacing on PC and mobile --}}
    @if($featuredProjects->isNotEmpty())
    @php
        $featuredCardWidth = 280;
        $featuredGap = 24;
        $featuredStep = $featuredCardWidth + $featuredGap;
    @endphp
    <section class="mb-12 max-md:mb-10" style="margin-top: 3rem;">
        <div class="flex items-center justify-between mb-6 max-md:mb-4">
            <h2 class="text-xl font-semibold text-white">Featured Projects</h2>
            <a href="{{ route('guest.projects.index') }}" class="text-sky-400 hover:text-sky-300 text-sm font-medium">View all</a>
        </div>
        <div class="relative" x-data="{ index: 0, total: {{ $featuredProjects->count() }}, step: {{ $featuredStep }}, go(i) { this.index = i; const t = $refs.track; if (t) t.scrollTo({ left: i * this.step, behavior: 'smooth' }); } }" x-init="$refs.track && $watch('index', v => { const t = $refs.track; if (t) t.scrollTo({ left: v * {{ $featuredStep }}, behavior: 'smooth' }); })">
            <div class="overflow-hidden rounded-2xl w-full">
                <div class="flex items-stretch overflow-x-auto snap-x snap-mandatory scroll-smooth pb-2 pl-0.5 pr-4 scrollbar-hide" style="scrollbar-width: none; -ms-overflow-style: none; gap: {{ $featuredGap }}px;" x-ref="track">
                    @foreach($featuredProjects as $project)
                        @php
                            $path = $project->featured_image_path ? trim($project->featured_image_path) : null;
                            $isExternal = $path && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'));
                            $isPlaceholder = $path && (str_contains(strtolower($path), 'placeholder') || str_contains(strtolower($path), 'dummyimage') || str_contains(strtolower($path), 'placehold.'));
                            $showImg = $path && !$isPlaceholder;
                            $imgUrl = $showImg ? ($isExternal ? $path : asset($path)) : null;
                        @endphp
                        <a href="{{ route('guest.projects.show', $project) }}" class="flex-shrink-0 w-[280px] min-w-[280px] max-w-[280px] snap-start group flex flex-col">
                            <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-xl overflow-hidden shadow-lg hover:shadow-xl hover:border-slate-600/80 transition-all duration-300 hover:-translate-y-1 flex flex-col flex-1 min-h-0 w-full">
                                <div class="relative w-full rounded-t-xl overflow-hidden bg-slate-700/80 shrink-0" style="aspect-ratio: 16/9;">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $project->project_name }}" class="absolute inset-0 w-full h-full object-cover object-center min-w-0 min-h-0 group-hover:scale-105 transition-transform duration-500" onerror="this.onerror=null; this.style.display='none'; var f=this.nextElementSibling; if(f) f.style.display='flex';">
                                        <div class="absolute inset-0 flex items-center justify-center bg-slate-700/90 text-slate-500" style="display: none;">
                                            <svg class="w-12 h-12 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                                        </div>
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center text-slate-500">
                                            <svg class="w-12 h-12 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4 flex flex-col flex-1 min-h-0">
                                    <h3 class="font-semibold text-white group-hover:text-sky-400 transition truncate">{{ $project->project_name }}</h3>
                                    <p class="text-slate-400 text-sm mt-1 line-clamp-2">{{ $project->short_description ?: Str::limit($project->project_name . ' – ' . ($project->project_type ?? 'Project'), 80) }}</p>
                                    @if($project->tech_stack)
                                        <div class="flex flex-wrap gap-1.5 mt-3">
                                            @foreach(array_map('trim', explode(',', $project->tech_stack)) as $tech)
                                                @if($tech)
                                                    <span class="px-2 py-0.5 rounded-lg text-xs font-medium bg-slate-600/80 text-slate-300 border border-slate-500/50">{{ $tech }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    <span class="inline-flex items-center gap-1 mt-auto pt-3 text-sky-400 text-sm font-medium group-hover:gap-2 transition-all">View Project →</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @if($featuredProjects->count() > 1)
            <div class="flex justify-center gap-2 mt-4">
                @foreach($featuredProjects as $i => $p)
                    <button type="button" @click="go({{ $i }})" :class="index === {{ $i }} ? 'bg-sky-500 w-6' : 'bg-slate-600 w-2'" class="h-2 rounded-full transition-all duration-300" aria-label="Slide {{ $i + 1 }}"></button>
                @endforeach
            </div>
            @endif
        </div>
    </section>
    @endif

    {{-- Hot Offers: horizontal only on PC (md and up), vertical stack on mobile --}}
    @if($hotOffers->isNotEmpty())
    <section class="mt-2 mb-12 max-md:mb-10">
        <h2 class="text-xl font-semibold text-white mb-6 max-md:mb-4">Hot Offers</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-8">
            @php $mid = (int) floor($hotOffers->count() / 2); @endphp
            @foreach($hotOffers as $i => $offer)
                @php $isBestValue = $i === $mid && $hotOffers->count() >= 2; @endphp
                <div class="relative rounded-2xl overflow-hidden {{ $isBestValue ? 'ring-2 ring-cyan-500/50 shadow-lg shadow-cyan-500/20 scale-[1.02] max-md:scale-100 max-md:ring-2' : '' }}">
                    <div class="relative bg-slate-800/70 backdrop-blur border border-slate-700/50 rounded-2xl p-6 h-full flex flex-col hover:border-slate-600/80 transition-all duration-300 hover:-translate-y-0.5 {{ $isBestValue ? 'border-cyan-500/30' : '' }}">
                        @if($isBestValue)
                            <div class="flex justify-end mb-2">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold text-white shrink-0" style="background-color: #0DA8E5;">Best value</span>
                            </div>
                        @endif
                        <h3 class="text-lg font-semibold text-white mb-2">{{ $offer->title }}</h3>
                        @if($offer->description)
                            <div class="hot-offer-description flex-1 mb-4">{!! $offer->formatted_description !!}</div>
                        @endif
                        @if($offer->price !== null)
                            <p class="text-2xl font-bold text-cyan-400 mb-4">৳{{ number_format($offer->price, 0) }}</p>
                        @endif
                        <a href="{{ route('guest.contact') }}" class="guest-hot-offer-cta inline-flex items-center justify-center gap-2 w-full py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-sky-500 hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20 transition-all duration-300 hover:shadow-cyan-500/30">
                            {{ $offer->cta_text }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Testimonials carousel --}}
    @if($testimonials->isNotEmpty())
    <section class="mb-12 max-md:mb-10">
        <h2 class="text-xl font-semibold text-white mb-6 max-md:mb-4">What Clients Say</h2>
        <div class="relative" x-data="testimonialsCarousel()">
            <div class="overflow-hidden rounded-2xl">
                @foreach($testimonials as $i => $t)
                    <div x-show="index === {{ $i }}" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl p-6 shadow-lg max-md:p-4">
                        <div class="flex gap-1 mb-3 text-amber-400">
                            @for($s = 0; $s < 5; $s++) <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> @endfor
                        </div>
                        <p class="text-slate-300 text-base leading-relaxed">"{{ $t->feedback }}"</p>
                        <div class="flex items-center gap-3 mt-4 pt-4 border-t border-slate-700/50">
                            @if($t->photo)
                                <img src="{{ str_starts_with($t->photo, 'http') ? $t->photo : asset($t->photo) }}" alt="{{ $t->client_name }}" class="w-10 h-10 rounded-full object-cover border border-slate-600">
                            @else
                                <div class="w-10 h-10 rounded-full bg-sky-500/20 flex items-center justify-center text-sky-400 font-semibold">{{ strtoupper(substr($t->client_name, 0, 1)) }}</div>
                            @endif
                            <span class="font-semibold text-white">{{ $t->client_name }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($testimonials->count() > 1)
            <div class="flex items-center justify-center gap-3 mt-4">
                <button type="button" @click="prev()" class="p-2 rounded-xl bg-slate-800/80 border border-slate-600 text-slate-300 hover:text-white hover:border-slate-500 transition" aria-label="Previous">←</button>
                <span class="text-slate-500 text-sm" x-text="(index + 1) + ' / ' + total"></span>
                <button type="button" @click="next()" class="p-2 rounded-xl bg-slate-800/80 border border-slate-600 text-slate-300 hover:text-white hover:border-slate-500 transition" aria-label="Next">→</button>
            </div>
            @endif
        </div>
    </section>
    @endif

    {{-- Start Project card at bottom – CTA to contact --}}
    <section class="mb-8 max-md:mb-6">
        <a href="{{ route('guest.contact') }}" class="block rounded-2xl overflow-hidden bg-gradient-to-br from-cyan-900/40 via-slate-800/80 to-sky-900/40 border border-slate-700/50 hover:border-cyan-500/40 shadow-lg hover:shadow-cyan-500/10 transition-all duration-300 hover:-translate-y-0.5 group">
            <div class="relative px-6 py-8 max-md:px-4 max-md:py-6 text-center">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_70%_50%_at_50%_50%,rgba(6,182,212,0.15),transparent)]"></div>
                <div class="relative flex flex-col items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center group-hover:bg-cyan-500/30 transition">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h2 class="text-xl font-semibold text-white">Start a Project</h2>
                    <p class="text-slate-400 text-sm max-w-md">Have an idea? Get in touch and we’ll help you build it—from concept to launch.</p>
                    <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-sky-500 group-hover:from-cyan-400 group-hover:to-sky-400 transition-all">
                        Start Project
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </span>
                </div>
            </div>
        </a>
    </section>

    <script>
        document.addEventListener('alpine:init', function () {
            Alpine.data('guestStats', function () {
                const totalPublicProjects = {{ $totalPublicProjects }};
                const runningPublicProjects = {{ $runningPublicProjects }};
                const duration = 1200;
                const steps = 30;
                const interval = duration / steps;
                return {
                    totalPublicProjects: 0,
                    runningPublicProjects: 0,
                    init() {
                        const anim = (key, end) => {
                            let step = 0;
                            const inc = end / steps;
                            const t = setInterval(() => {
                                step++;
                                this[key] = Math.min(Math.round(inc * step), end);
                                if (step >= steps) clearInterval(t);
                            }, interval);
                        };
                        anim('totalPublicProjects', totalPublicProjects);
                        anim('runningPublicProjects', runningPublicProjects);
                    }
                };
            });
            Alpine.data('testimonialsCarousel', function () {
                var total = {{ $testimonials->count() }};
                return {
                    index: 0,
                    total: total,
                    next() {
                        this.index = (this.index + 1) % this.total;
                    },
                    prev() {
                        this.index = (this.index - 1 + this.total) % this.total;
                    },
                    init() {
                        if (this.total <= 1) return;
                        var self = this;
                        setInterval(function () { self.next(); }, 6000);
                    }
                };
            });
        });
    </script>
</x-guest-portal-layout>
