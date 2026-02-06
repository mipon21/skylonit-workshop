<x-app-layout>
    <x-slot name="title">Sign Contract</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <a href="{{ route('projects.show', $project) }}#contracts" class="text-slate-400 hover:text-white text-sm">← Back to project</a>
        <h1 class="text-2xl font-semibold text-white">Sign Contract</h1>
        <p class="text-slate-400 text-sm">Project: {{ $project->project_name }}</p>

        <div class="bg-slate-800/80 border border-slate-700/50 rounded-2xl p-6">
            <form action="{{ route('projects.contracts.sign', [$project, $contract]) }}" method="POST" id="sign-form" x-data="signForm()" x-init="init()">
                @csrf
                <div class="space-y-6">
                    <div>
                        <p class="text-sm font-medium text-slate-400 mb-2">Draw your signature (optional)</p>
                        <div class="border-2 border-slate-600 rounded-xl bg-white overflow-hidden" style="max-width: 100%;">
                            <canvas id="signature-canvas" width="500" height="180" class="block w-full touch-none cursor-crosshair"
                                style="max-width: 100%; height: auto; min-height: 180px;"></canvas>
                        </div>
                        <button type="button" @click="clearCanvas()" class="mt-2 text-sm text-slate-400 hover:text-white">Clear</button>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Or type your full name *</label>
                        <input type="text" name="signature_text" id="signature_text" value="{{ old('signature_text', auth()->user()->name) }}"
                            class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500"
                            placeholder="Your full name" required>
                        <input type="hidden" name="signature_data" id="signature_data" value="">
                    </div>
                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="agree" id="agree" value="1" required
                            class="mt-1 rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                        <label for="agree" class="text-sm text-slate-300">I agree to this contract and sign it voluntarily.</label>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('projects.show', $project) }}#contracts" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Sign Contract</button>
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
