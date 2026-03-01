@if($invoices->count() > 0 || ($supportInvoices ?? collect())->count() > 0)
    <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden max-md:overflow-x-auto">
        <div class="overflow-x-auto">
            <table class="w-full max-md:min-w-[640px]">
                <thead>
                    <tr class="border-b theme-border">
                        <th class="px-6 py-4 text-left text-xs font-medium theme-text-secondary uppercase tracking-wider">Invoice Number</th>
                        <th class="px-6 py-4 text-left text-xs font-medium theme-text-secondary uppercase tracking-wider">Project</th>
                        <th class="px-6 py-4 text-left text-xs font-medium theme-text-secondary uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-medium theme-text-secondary uppercase tracking-wider">Invoice Date</th>
                        <th class="px-6 py-4 text-left text-xs font-medium theme-text-secondary uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-medium theme-text-secondary uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-medium theme-text-secondary uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    @foreach($invoices as $invoice)
                        <tr class="theme-sidebar-link-hover/20 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium theme-text-primary">{{ $invoice->invoice_number }}</div></td>
                            <td class="px-6 py-4">
                                <div class="text-sm theme-text-primary">{{ $invoice->project->project_name }}</div>
                                <div class="text-xs theme-text-secondary">{{ $invoice->project->project_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs theme-bg-tertiary/50 theme-text-secondary">Project</span></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm theme-text-secondary">{{ $invoice->invoice_date->format('M d, Y') }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium theme-text-primary">৳ {{ number_format($invoice->payment->amount, 2) }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span @class([
                                    'px-3 py-1 rounded-full text-xs font-medium inline-flex items-center gap-1',
                                    'bg-emerald-500/20 text-emerald-400' => $invoice->payment_status === 'PAID',
                                    'bg-amber-500/20 text-amber-400' => $invoice->payment_status === 'PARTIAL',
                                    'bg-red-500/20 text-red-400' => $invoice->payment_status === 'DUE',
                                ])>{{ $invoice->payment_status }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('invoices.view', $invoice) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium inline-flex items-center gap-1">View</a>
                                    <a href="{{ route('invoices.download', $invoice) }}" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-medium inline-flex items-center gap-1">Download</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @foreach(($supportInvoices ?? collect()) as $sp)
                        <tr class="theme-sidebar-link-hover/20 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium theme-text-primary">{{ $sp->invoice_number }}</div></td>
                            <td class="px-6 py-4">
                                <div class="text-sm theme-text-primary">{{ $sp->project->project_name }}</div>
                                <div class="text-xs theme-text-secondary">{{ $sp->project->project_code ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs bg-violet-500/20 text-violet-400">Support</span></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm theme-text-secondary">{{ $sp->paid_at?->format('M d, Y') ?? '—' }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium theme-text-primary">৳ {{ number_format($sp->amount, 2) }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-medium inline-flex items-center gap-1 bg-emerald-500/20 text-emerald-400">PAID</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('support-packages.view-invoice', [$sp->project, $sp]) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium inline-flex items-center gap-1">View</a>
                                    <a href="{{ route('support-packages.download-invoice', [$sp->project, $sp]) }}" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-medium inline-flex items-center gap-1">Download</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
        <div class="theme-card-bg-only theme-border border rounded-2xl px-6 py-4">{{ $invoices->links() }}</div>
    @endif
@else
    <div class="theme-card-bg-only theme-border border rounded-2xl p-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full theme-bg-tertiary/50 mb-4">
            <svg class="w-8 h-8 theme-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h3 class="text-lg font-semibold theme-text-primary mb-2">No Invoices Yet</h3>
        <p class="theme-text-secondary">Invoices will appear here when payments are recorded for your projects (including support packages).</p>
    </div>
@endif
