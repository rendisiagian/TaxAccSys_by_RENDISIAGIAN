<x-app-layout>
@section('title', 'PPh 21 Monthly (TER)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">PPh 21 Monthly (TER)</h2>
            <p class="text-sm text-slate-500 mt-1">Calculate monthly income tax using TER rates.</p>
        </div>
        <div class="flex items-center space-x-3">
            <form action="{{ route('taxes.pph21.index') }}" method="GET" class="flex items-center space-x-2">
                <select name="month" class="rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                    @endfor
                </select>
                <select name="year" class="rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2">
                    @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
                <button type="submit" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
                    View
                </button>
            </form>
            
            <form action="{{ route('taxes.pph21.journal') }}" method="POST" onsubmit="return confirm('Generate journal for all draft records in this period?');">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium flex items-center shadow-sm">
                    <i data-lucide="book" class="w-4 h-4 mr-2"></i> Auto Journal
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ route('taxes.pph21.calculate') }}" method="POST">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">

            <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider">Gross Income Input Grid</h3>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium flex items-center">
                    <i data-lucide="calculator" class="w-4 h-4 mr-2"></i> Calculate & Save Draft
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="px-6 py-3 font-semibold">Employee</th>
                            <th class="px-6 py-3 font-semibold">Category</th>
                            <th class="px-6 py-3 font-semibold text-right">Gross Income (Bruto)</th>
                            <th class="px-6 py-3 font-semibold text-right">TER Rate</th>
                            <th class="px-6 py-3 font-semibold text-right">Tax Amount</th>
                            <th class="px-6 py-3 font-semibold text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($employees as $index => $employee)
                            @php
                                $record = $records->get($employee->id);
                            @endphp
                        <tr class="hover:bg-slate-50 transition-colors {{ $record && $record->status === 'approved' ? 'bg-slate-50/50' : '' }}">
                            <td class="px-6 py-3">
                                <input type="hidden" name="employees[{{ $index }}][id]" value="{{ $employee->id }}">
                                <div class="font-medium text-slate-800">{{ $employee->name }}</div>
                                <div class="text-xs text-slate-500">{{ $employee->nik ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700 uppercase tracking-wider">
                                    {{ $employee->ptkp_status }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-700 uppercase tracking-wider ml-1">
                                    TER {{ $employee->ter_category }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @if($record && $record->status === 'approved')
                                    <div class="text-right font-mono text-slate-700">Rp {{ number_format($record->gross_income, 2, ',', '.') }}</div>
                                    <input type="hidden" name="employees[{{ $index }}][gross_income]" value="0"> {{-- Prevent update --}}
                                @else
                                    <input type="number" step="0.01" min="0" name="employees[{{ $index }}][gross_income]" value="{{ $record ? $record->gross_income : '' }}" class="w-full text-right font-mono rounded border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 text-sm" placeholder="0.00">
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                @if($record && $record->terRate)
                                    <span class="font-mono text-slate-600 bg-slate-100 px-2 py-1 rounded">{{ $record->terRate->rate_percentage }}%</span>
                                @else
                                    <span class="text-slate-400 italic text-xs">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                @if($record)
                                    <div class="font-mono font-medium text-slate-800">Rp {{ number_format($record->tax_amount, 2, ',', '.') }}</div>
                                @else
                                    <span class="text-slate-400 font-mono">0.00</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($record)
                                    @if($record->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-700">
                                            <i data-lucide="check" class="w-3 h-3 mr-1"></i> Journaled
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">
                                            Draft
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                No active employees found. Please add employees first.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
@endsection
</x-app-layout>
