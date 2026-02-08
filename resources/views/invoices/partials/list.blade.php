@if($invoices->count() > 0)
    <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden max-md:overflow-x-auto">
        <div class="overflow-x-auto">
            <table class="w-full max-md:min-w-[640px]">
                <thead>
                    <tr class="border-b border-slate-700/50">
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Invoice Number</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Invoice Date</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    @foreach($invoices as $invoice)
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-white">{{ $invoice->invoice_number }}</div></td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-white">{{ $invoice->project->project_name }}</div>
                                <div class="text-xs text-slate-400">{{ $invoice->project->project_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-slate-300">{{ $invoice->invoice_date->format('M d, Y') }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-white">৳ {{ number_format($invoice->payment->amount, 2) }}</div></td>
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
                                    <a href="{{ route('invoices.view', $invoice) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-xs font-medium inline-flex items-center gap-1">View</a>
                                    <a href="{{ route('invoices.download', $invoice) }}" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-medium inline-flex items-center gap-1">Download</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl px-6 py-4">{{ $invoices->links() }}</div>
    @endif
@else
    <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-700/50 mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No Invoices Yet</h3>
        <p class="text-slate-400">Invoices will appear here when payments are recorded for your projects.</p>
    </div>
@endif
