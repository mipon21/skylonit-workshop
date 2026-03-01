<x-app-layout>
    <x-slot name="title">Invoices</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold theme-text-primary">Invoices</h1>
            <form action="{{ route('invoices.index') }}" method="GET" class="flex items-center gap-2 max-w-md flex-1 min-w-0" x-data="invoiceLiveSearch()">
                <label for="invoice-search" class="sr-only">Search</label>
                <input type="search" name="search" id="invoice-search" value="{{ request('search') }}"
                       placeholder="Search by invoice #, project #, project name, client name..."
                       class="flex-1 min-w-0 rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 text-sm theme-input-placeholder theme-input-focus"
                       @input.debounce.400ms="fetchResults()"
                       autocomplete="off">
            </form>
        </div>

        <div id="invoices-list">
            @include('invoices.partials.list', ['invoices' => $invoices, 'supportInvoices' => $supportInvoices ?? collect()])
        </div>

        <script>
            document.addEventListener('alpine:init', function() {
                Alpine.data('invoiceLiveSearch', function() {
                    return {
                        fetchResults() {
                            var form = this.$el.closest('form');
                            var url = new URL(form.action);
                            url.searchParams.set('search', (form.querySelector('[name=search]') || {}).value || '');
                            fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                                .then(function(r) { return r.text(); })
                                .then(function(html) {
                                    var el = document.getElementById('invoices-list');
                                    if (el) el.innerHTML = html;
                                    history.replaceState(null, '', url.toString());
                                });
                        }
                    };
                });
            });
        </script>
    </div>
</x-app-layout>
