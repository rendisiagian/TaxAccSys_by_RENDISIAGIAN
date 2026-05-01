<x-app-layout>
@section('title', 'Trial Balance')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Trial Balance</h2>
            <p class="text-sm text-slate-500 mt-1">Summary of account balances as of {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}.</p>
        </div>
        <button type="button" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium shadow-sm" onclick="window.print()">
            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print Report
        </button>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <form action="{{ route('reports.trial_balance') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                Update Report
            </button>
        </form>
    </div>

    {{-- Report Content --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden print-section">
        <div class="p-6 border-b border-slate-200 bg-slate-50 text-center">
            <h3 class="text-lg font-bold text-slate-800 uppercase tracking-wider">{{ $currentCompany->name }}</h3>
            <p class="text-md font-semibold text-slate-700 mt-1">Trial Balance</p>
            <p class="text-xs text-slate-500 mt-1">For the period {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-3 font-semibold w-32">Account Code</th>
                        <th class="px-6 py-3 font-semibold">Account Name</th>
                        <th class="px-6 py-3 font-semibold text-right w-48">Debit</th>
                        <th class="px-6 py-3 font-semibold text-right w-48">Credit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($trialBalance as $row)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-slate-600">{{ $row['account']->account_code }}</td>
                            <td class="px-6 py-3 text-slate-800">{{ $row['account']->account_name }}</td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700">
                                {{ $row['debit'] > 0 ? number_format($row['debit'], 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700">
                                {{ $row['credit'] > 0 ? number_format($row['credit'], 2, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500 italic">No activity for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 border-t-2 border-slate-200 text-sm font-bold text-slate-800 font-mono">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-right uppercase tracking-wider text-xs">Total</td>
                        <td class="px-6 py-4 text-right border-b-[3px] border-double border-slate-800">{{ number_format($totalDebit, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right border-b-[3px] border-double border-slate-800">{{ number_format($totalCredit, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        @if(abs($totalDebit - $totalCredit) > 0.01)
        <div class="p-4 bg-rose-50 border-t border-rose-200 text-rose-700 text-sm font-medium flex items-center justify-center">
            <i data-lucide="alert-triangle" class="w-5 h-5 mr-2"></i>
            Warning: Trial Balance is not balanced. Difference: Rp {{ number_format(abs($totalDebit - $totalCredit), 2, ',', '.') }}
        </div>
        @endif
    </div>
</div>
@endsection
</x-app-layout>
