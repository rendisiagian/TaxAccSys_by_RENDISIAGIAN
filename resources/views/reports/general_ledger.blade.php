<x-app-layout>
@section('title', __('General Ledger'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">{{ __('General Ledger') }}</h2>
            <p class="text-sm text-slate-500 mt-1">View detailed transactions for specific accounts.</p>
        </div>
        <button type="button" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium shadow-sm" onclick="window.print()">
            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print Report
        </button>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <form action="{{ route('reports.general_ledger') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Select Account <span class="text-red-500">*</span></label>
                <select name="account_id" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">-- Choose Account --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->account_code }} - {{ $account->account_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <button type="submit" class="mb-0 px-4 py-2 h-10 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center" title="Filter">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- Report Content --}}
    @if($selectedAccount)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden print-section">
        <div class="p-6 border-b border-slate-200 bg-slate-50 text-center">
            <h3 class="text-lg font-bold text-slate-800 uppercase tracking-wider">{{ $selectedAccount->account_name }}</h3>
            <p class="text-sm font-mono text-slate-600 mt-1">Account No: {{ $selectedAccount->account_code }}</p>
            <p class="text-xs text-slate-500 mt-1">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-3 font-semibold w-32">Date</th>
                        <th class="px-6 py-3 font-semibold w-40">Voucher</th>
                        <th class="px-6 py-3 font-semibold">Description</th>
                        <th class="px-6 py-3 font-semibold text-right w-32">Debit</th>
                        <th class="px-6 py-3 font-semibold text-right w-32">Credit</th>
                        <th class="px-6 py-3 font-semibold text-right w-36">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    {{-- Opening Balance --}}
                    <tr class="bg-slate-50 font-medium">
                        <td colspan="3" class="px-6 py-3 text-slate-600 italic">Opening Balance as of {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-right text-slate-400 font-mono">-</td>
                        <td class="px-6 py-3 text-right text-slate-400 font-mono">-</td>
                        <td class="px-6 py-3 text-right text-slate-800 font-mono">
                            {{ $openingBalance < 0 ? '(' . number_format(abs($openingBalance), 2, ',', '.') . ')' : number_format($openingBalance, 2, ',', '.') }}
                        </td>
                    </tr>

                    {{-- Transactions --}}
                    @php
                        $runningBalance = $openingBalance;
                        $totalDebit = 0;
                        $totalCredit = 0;
                    @endphp

                    @forelse($lines as $line)
                        @php
                            $totalDebit += $line->debit;
                            $totalCredit += $line->credit;
                            
                            if ($selectedAccount->normal_balance === 'debit') {
                                $runningBalance += ($line->debit - $line->credit);
                            } else {
                                $runningBalance += ($line->credit - $line->debit);
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 text-slate-600">{{ $line->journalEntry->date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 font-mono text-slate-500">
                                <a href="{{ route('journals.show', $line->journalEntry) }}" class="hover:text-indigo-600 hover:underline">
                                    {{ $line->journalEntry->voucher_number }}
                                </a>
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-slate-800">{{ $line->journalEntry->description }}</div>
                                @if($line->description)
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $line->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700">
                                {{ $line->debit > 0 ? number_format($line->debit, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-3 text-right font-mono text-slate-700">
                                {{ $line->credit > 0 ? number_format($line->credit, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-3 text-right font-mono text-slate-800 font-medium">
                                {{ $runningBalance < 0 ? '(' . number_format(abs($runningBalance), 2, ',', '.') . ')' : number_format($runningBalance, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500 italic">No transactions found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 border-t-2 border-slate-200 text-sm font-bold text-slate-800 font-mono">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right uppercase tracking-wider text-xs">Total Movements</td>
                        <td class="px-6 py-3 text-right">{{ number_format($totalDebit, 2, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right">{{ number_format($totalCredit, 2, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right bg-slate-200">
                            {{ $runningBalance < 0 ? '(' . number_format(abs($runningBalance), 2, ',', '.') . ')' : number_format($runningBalance, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl border border-slate-200 border-dashed p-12 text-center text-slate-500">
        <i data-lucide="book" class="w-12 h-12 mx-auto text-slate-300 mb-4"></i>
        <p>Select an account and date range to view the General Ledger.</p>
    </div>
    @endif
</div>
@endsection
</x-app-layout>
