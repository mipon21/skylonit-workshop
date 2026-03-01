<x-app-layout>
    <x-slot name="title">Payments</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold theme-text-primary">Payments</h1>
            <form action="{{ route('client.payments.index') }}" method="GET" class="flex flex-wrap items-center gap-2 max-w-2xl flex-1 min-w-0" x-data="paymentLiveSearch()">
                <label for="payment-search" class="sr-only">Search</label>
                <input type="search" name="search" id="payment-search" value="{{ request('search') }}"
                       placeholder="Search by invoice #, project #, project name, client name..."
                       class="flex-1 min-w-[200px] rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 text-sm theme-input-placeholder theme-input-focus"
                       @input.debounce.400ms="fetchResults()"
                       autocomplete="off">
                <select name="status" class="rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 text-sm theme-input-focus"
                        @change="fetchResults()">
                    <option value="" {{ request('status') === '' ? 'selected' : '' }}>All</option>
                    <option value="due" {{ request('status') === 'due' ? 'selected' : '' }}>Due</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </form>
        </div>

        <div id="payments-list">
            @include('client-payments.partials.list', ['payments' => $payments, 'supportPackages' => $supportPackages ?? collect()])
        </div>

        <script>
            document.addEventListener('alpine:init', function() {
                Alpine.data('paymentLiveSearch', function() {
                    return {
                        fetchResults() {
                            var form = this.$el.closest('form');
                            var url = new URL(form.action);
                            url.searchParams.set('search', (form.querySelector('[name=search]') || {}).value || '');
                            var status = (form.querySelector('[name=status]') || {}).value || '';
                            if (status) url.searchParams.set('status', status);
                            fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                                .then(function(r) { return r.text(); })
                                .then(function(html) {
                                    var el = document.getElementById('payments-list');
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
