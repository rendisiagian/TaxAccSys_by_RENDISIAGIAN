<x-app-layout>
@section('title', 'VAT / PPN')

@section('content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Value Added Tax (PPN)</h2>
            <p class="text-sm text-slate-500 mt-1">Manage Faktur Pajak Masukan (In) and Keluaran (Out).</p>
        </div>
        <a href="{{ route('taxes.ppn.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Record Faktur
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 font-semibold">Date & Faktur No</th>
                    <th class="px-6 py-4 font-semibold">Counterpart</th>
                    <th class="px-6 py-4 font-semibold">Type</th>
                    <th class="px-6 py-4 font-semibold text-right">DPP</th>
                    <th class="px-6 py-4 font-semibold text-right">PPN</th>
                    <th class="px-6 py-4 font-semibold text-center">{{ __('Status') }}</th>
                    <th class="px-6 py-4 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm">
                @forelse($transactions as $trx)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-800">{{ $trx->transaction_date->format('d/m/Y') }}</div>
                        <div class="text-slate-500 text-xs mt-0.5 font-mono">{{ $trx->document_number ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-800">{{ $trx->counterpart_name }}</div>
                        <div class="text-slate-500 text-xs mt-0.5 font-mono">NPWP: {{ $trx->counterpart_tin ?? '-' }}</div>
                        @if($trx->project_id)
                            <div class="text-indigo-600 text-xs mt-1 font-medium"><i data-lucide="folder-kanban" class="w-3 h-3 inline"></i> {{ $trx->project->name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($trx->tax_type === 'ppn_out')
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-emerald-100 text-emerald-700 uppercase tracking-wider">
                                Keluaran (Out)
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-700 uppercase tracking-wider">
                                Masukan (In)
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="text-slate-800 font-mono">Rp {{ number_format($trx->tax_base, 2, ',', '.') }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="font-medium text-slate-800 font-mono">Rp {{ number_format($trx->tax_amount, 2, ',', '.') }}</div>
                        <div class="text-slate-500 text-xs mt-0.5">Rate: {{ floatval($trx->tax_rate) }}%</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($trx->status === 'approved')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            Journaled
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                            Draft
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($trx->status !== 'approved')
                        <form action="{{ route('taxes.transactions.journal', $trx) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="text-slate-600 hover:text-indigo-600 transition-colors flex items-center text-xs font-medium bg-slate-100 hover:bg-indigo-50 px-2 py-1 rounded border border-slate-200">
                                <i data-lucide="book" class="w-3 h-3 mr-1"></i> Auto Journal
                            </button>
                        </form>
                        @else
                            @if($trx->journal_entry_id)
                            <a href="{{ route('journals.show', $trx->journal_entry_id) }}" class="text-indigo-600 hover:underline text-xs font-medium">
                                View Journal
                            </a>
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        {{ __('No data found.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($transactions->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection
</x-app-layout>
