<x-app-layout>
    <x-slot name="title">Sign Contract</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <a href="{{ route('projects.show', $project) }}#contracts" class="theme-text-secondary theme-hover-primary text-sm">← Back to project</a>
        <h1 class="text-2xl font-semibold theme-text-primary">Sign Contract</h1>
        <p class="theme-text-secondary text-sm">Project: {{ $project->project_name }}</p>

        <div class="theme-bg-tertiary/80 border theme-border rounded-2xl p-6">
            <form action="{{ route('projects.contracts.sign', [$project, $contract]) }}" method="POST" id="sign-form" x-data="signForm()" x-init="init()">
                @csrf
                <div class="space-y-6">
                    <div>
                        <p class="text-sm font-medium theme-text-secondary mb-2">Draw your signature (optional)</p>
                        <div class="border-2 theme-border rounded-xl bg-white overflow-hidden" style="max-width: 100%;">
                            <canvas id="signature-canvas" width="500" height="180" class="block w-full touch-none cursor-crosshair"
                                style="max-width: 100%; height: auto; min-height: 180px;"></canvas>
                        </div>
                        <button type="button" @click="clearCanvas()" class="mt-2 text-sm theme-text-secondary theme-hover-primary">Clear</button>
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Or type your full name *</label>
                        <input type="text" name="signature_text" id="signature_text" value="{{ old('signature_text', auth()->user()->name) }}"
                            class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500"
                            placeholder="Your full name" required>
                        <input type="hidden" name="signature_data" id="signature_data" value="">
                    </div>
                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="agree" id="agree" value="1" required
                            class="mt-1 rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                        <label for="agree" class="text-sm theme-text-secondary">I agree to this contract and sign it voluntarily.</label>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('projects.show', $project) }}#contracts" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</a>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium">Sign Contract</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function signForm() {
            let canvas, ctx, drawing = false;
            return {
                init() {
                    canvas = document.getElementById('signature-canvas');
                    if (!canvas) return;
                    ctx = canvas.getContext('2d');
                    ctx.strokeStyle = '#000';
                    ctx.lineWidth = 2;
                    ctx.lineCap = 'round';
                    const rect = canvas.getBoundingClientRect();
                    const scaleX = canvas.width / rect.width;
                    const scaleY = canvas.height / rect.height;
                    const getXY = (e) => {
                        const r = canvas.getBoundingClientRect();
                        const sx = canvas.width / r.width;
                        const sy = canvas.height / r.height;
                        const ev = e.touches ? e.touches[0] : e;
                        return { x: (ev.clientX - r.left) * sx, y: (ev.clientY - r.top) * sy };
                    };
                    const start = (e) => { e.preventDefault(); drawing = true; const p = getXY(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); };
                    const move = (e) => { e.preventDefault(); if (!drawing) return; const p = getXY(e); ctx.lineTo(p.x, p.y); ctx.stroke(); };
                    const end = () => { drawing = false; };
                    canvas.addEventListener('mousedown', start);
                    canvas.addEventListener('mousemove', move);
                    canvas.addEventListener('mouseup', end);
                    canvas.addEventListener('mouseleave', end);
                    canvas.addEventListener('touchstart', start, { passive: false });
                    canvas.addEventListener('touchmove', move, { passive: false });
                    canvas.addEventListener('touchend', end);
                },
                clearCanvas() {
                    if (!ctx || !canvas) return;
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    document.getElementById('signature_data').value = '';
                }
            };
        }
        document.getElementById('sign-form').addEventListener('submit', function() {
            const canvas = document.getElementById('signature-canvas');
            const input = document.getElementById('signature_data');
            if (canvas) {
                try {
                    input.value = canvas.toDataURL('image/png');
                } catch (e) {}
            }
        });
    </script>
</x-app-layout>
