<x-app-layout>
@section('title', __('Financial Statements'))

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">{{ __('Financial Statements') }}</h2>
        <p class="text-sm text-slate-500 mt-1">Income Statement & Balance Sheet.</p>
    </div>
</div>

<!-- Filter Card -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-6 p-5">
    <form action="{{ route('reports.financial_statements') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium h-[38px] flex items-center">
            <i data-lucide="filter" class="w-4 h-4 mr-2"></i> Generate
        </button>
    </form>
</div>

<!-- Tabs -->
<div x-data="{ tab: 'income_statement' }" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="border-b border-slate-200">
        <nav class="flex -mb-px px-6" aria-label="Tabs">
            <button @click="tab = 'income_statement'" 
                :class="{'border-indigo-500 text-indigo-600': tab === 'income_statement', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': tab !== 'income_statement'}"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm w-1/2 text-center transition-colors">
                Income Statement (P&L)
            </button>
            <button @click="tab = 'balance_sheet'" 
                :class="{'border-indigo-500 text-indigo-600': tab === 'balance_sheet', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': tab !== 'balance_sheet'}"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm w-1/2 text-center transition-colors">
                Balance Sheet
            </button>
        </nav>
    </div>

    <div class="p-6">
        <!-- Income Statement -->
        <div x-show="tab === 'income_statement'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="text-center mb-8">
                <h3 class="text-xl font-bold text-slate-800">INCOME STATEMENT</h3>
                <p class="text-sm text-slate-500">For the period {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>

            <div class="max-w-4xl mx-auto border border-slate-200 rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-slate-800 font-semibold w-3/4">Account</th>
                            <th class="px-6 py-3 text-slate-800 font-semibold w-1/4 text-right">Amount (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- Revenue -->
                        <tr class="bg-slate-50/50">
                            <td colspan="2" class="px-6 py-2 font-semibold text-slate-700">REVENUE</td>
                        </tr>
                        @forelse($revenueAccounts as $acc)
                        <tr>
                            <td class="px-6 py-2 pl-10">{{ $acc['account']->account_code }} - {{ $acc['account']->name }}</td>
                            <td class="px-6 py-2 text-right">{{ number_format($acc['balance'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-2 pl-10 text-slate-500 italic">No revenue recorded.</td>
                        </tr>
                        @endforelse
                        <tr class="bg-indigo-50/30">
                            <td class="px-6 py-3 font-semibold text-slate-800 text-right">Total Revenue</td>
                            <td class="px-6 py-3 font-semibold text-slate-800 text-right">{{ number_format($totalRevenue, 2) }}</td>
                        </tr>

                        <!-- Expenses -->
                        <tr class="bg-slate-50/50">
                            <td colspan="2" class="px-6 py-2 font-semibold text-slate-700">EXPENSES</td>
                        </tr>
                        @forelse($expenseAccounts as $acc)
                        <tr>
                            <td class="px-6 py-2 pl-10">{{ $acc['account']->account_code }} - {{ $acc['account']->name }}</td>
                            <td class="px-6 py-2 text-right">{{ number_format($acc['balance'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-2 pl-10 text-slate-500 italic">No expenses recorded.</td>
                        </tr>
                        @endforelse
                        <tr class="bg-indigo-50/30">
                            <td class="px-6 py-3 font-semibold text-slate-800 text-right">Total Expenses</td>
                            <td class="px-6 py-3 font-semibold text-slate-800 text-right">{{ number_format($totalExpense, 2) }}</td>
                        </tr>

                        <!-- Net Income -->
                        <tr class="{{ $netIncome >= 0 ? 'bg-emerald-50' : 'bg-rose-50' }} border-t-2 border-slate-300">
                            <td class="px-6 py-4 font-bold text-slate-900 text-right">NET INCOME</td>
                            <td class="px-6 py-4 font-bold text-slate-900 text-right border-double border-b-4 border-slate-400">
                                {{ number_format($netIncome, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Balance Sheet -->
        <div x-show="tab === 'balance_sheet'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="text-center mb-8">
                <h3 class="text-xl font-bold text-slate-800">BALANCE SHEET</h3>
                <p class="text-sm text-slate-500">As of {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>

            <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Assets Side -->
                <div>
                    <h4 class="font-bold text-slate-800 mb-3 border-b-2 border-slate-800 pb-2">ASSETS</h4>
                    <table class="w-full text-sm text-left">
                        <tbody class="divide-y divide-slate-100">
                            @forelse($assetAccounts as $acc)
                            <tr>
                                <td class="py-2">{{ $acc['account']->account_code }} - {{ $acc['account']->name }}</td>
                                <td class="py-2 text-right">{{ number_format($acc['balance'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-2 text-slate-500 italic">No assets recorded.</td>
                            </tr>
                            @endforelse
                            <tr class="border-t-2 border-slate-300 mt-2">
                                <td class="py-3 font-bold text-slate-800">TOTAL ASSETS</td>
                                <td class="py-3 font-bold text-slate-800 text-right border-double border-b-4 border-slate-400">
                                    {{ number_format($totalAssets, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Liabilities & Equity Side -->
                <div>
                    <h4 class="font-bold text-slate-800 mb-3 border-b-2 border-slate-800 pb-2">LIABILITIES & EQUITY</h4>
                    <table class="w-full text-sm text-left">
                        <tbody class="divide-y divide-slate-100">
                            <tr class="bg-slate-50/50">
                                <td colspan="2" class="py-2 font-semibold text-slate-700">LIABILITIES</td>
                            </tr>
                            @forelse($liabilityAccounts as $acc)
                            <tr>
                                <td class="py-2 pl-4">{{ $acc['account']->account_code }} - {{ $acc['account']->name }}</td>
                                <td class="py-2 text-right">{{ number_format($acc['balance'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-2 pl-4 text-slate-500 italic">No liabilities recorded.</td>
                            </tr>
                            @endforelse
                            <tr class="bg-slate-50/50">
                                <td colspan="2" class="py-2 font-semibold text-slate-700 mt-4">EQUITY</td>
                            </tr>
                            @foreach($equityAccounts as $acc)
                            <tr>
                                <td class="py-2 pl-4">{{ $acc['account']->account_code }} - {{ $acc['account']->name }}</td>
                                <td class="py-2 text-right">{{ number_format($acc['balance'], 2) }}</td>
                            </tr>
                            @endforeach
                            <!-- Dynamic Retained Earnings to balance the sheet -->
                            <tr class="text-indigo-700">
                                <td class="py-2 pl-4 flex items-center">
                                    Current Year Earnings <i data-lucide="info" class="w-3 h-3 ml-1" title="Dynamically calculated from Income Statement"></i>
                                </td>
                                <td class="py-2 text-right">{{ number_format($totalRetainedEarnings, 2) }}</td>
                            </tr>
                            
                            <tr class="border-t-2 border-slate-300 mt-2">
                                <td class="py-3 font-bold text-slate-800">TOTAL LIABILITIES & EQUITY</td>
                                <td class="py-3 font-bold text-slate-800 text-right border-double border-b-4 border-slate-400">
                                    {{ number_format($totalLiabilities + $totalEquityIncludingRE, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    @if(round($totalAssets, 2) === round($totalLiabilities + $totalEquityIncludingRE, 2))
                    <div class="mt-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                        Balance Sheet is balanced!
                    </div>
                    @else
                    <div class="mt-4 p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-700 text-sm flex items-center">
                        <i data-lucide="alert-circle" class="w-4 h-4 mr-2"></i>
                        Balance Sheet is not balanced. Out of balance by: {{ number_format(abs($totalAssets - ($totalLiabilities + $totalEquityIncludingRE)), 2) }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
</x-app-layout>
