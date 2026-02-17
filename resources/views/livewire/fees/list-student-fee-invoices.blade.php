<div class="card">
    <div class="card-header">
        <h4 class="card-title">Student Fee Invoices</h4>
    </div>
    <div class="card-body">
        @if(isset($feeInvoices) && $feeInvoices?->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Invoice #</th>
                            <th class="px-4 py-2 text-left">Amount</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Due Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($feeInvoices as $invoice)
                            <tr>
                                <td class="px-4 py-2">{{ $invoice->invoice_number ?? $invoice->id }}</td>
                                <td class="px-4 py-2">{{ $invoice->total_amount ?? '-' }}</td>
                                <td class="px-4 py-2">{{ ucfirst($invoice->status ?? 'pending') }}</td>
                                <td class="px-4 py-2">{{ $invoice->due_date ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">No fee invoices found.</p>
        @endif
    </div>
</div>
