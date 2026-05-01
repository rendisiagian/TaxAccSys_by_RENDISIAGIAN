<x-app-layout>
@section('title', __('Tax Control'))

@section('content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Tax Control & Audits</h2>
            <p class="text-sm text-slate-500 mt-1">Track SP2DK, STP, SKPKB, and other tax documents.</p>
        </div>
        <a href="{{ route('tax-audits.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Record Document
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 font-semibold">Type & No</th>
                    <th class="px-6 py-4 font-semibold">Date & Period</th>
                    <th class="px-6 py-4 font-semibold text-right">Principal</th>
                    <th class="px-6 py-4 font-semibold text-right">Penalty</th>
                    <th class="px-6 py-4 font-semibold text-center">{{ __('Status') }}</th>
                    <th class="px-6 py-4 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm">
                @forelse($audits as $audit)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ in_array($audit->document_type, ['SKPKB', 'STP']) ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700' }} uppercase tracking-wider mb-1">
                            {{ $audit->document_type }}
                        </span>
                        <div class="text-slate-800 font-mono text-xs">{{ $audit->document_number }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-800">{{ $audit->document_date->format('d M Y') }}</div>
                        <div class="text-slate-500 text-xs mt-0.5">Tax Year: {{ $audit->tax_period_year }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="text-slate-800 font-mono">Rp {{ number_format($audit->principal_amount, 2, ',', '.') }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="text-rose-600 font-mono">Rp {{ number_format($audit->penalty_amount, 2, ',', '.') }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php
                            $statusColors = [
                                'received' => 'bg-amber-100 text-amber-700',
                                'responded' => 'bg-blue-100 text-blue-700',
                                'closed' => 'bg-emerald-100 text-emerald-700',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium uppercase tracking-wider {{ $statusColors[$audit->status] }}">
                            {{ $audit->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('tax-audits.edit', $audit) }}" class="text-slate-400 hover:text-indigo-600 transition-colors" title="{{ __('Edit') }}">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('tax-audits.destroy', $audit) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors" title="{{ __('Delete') }}">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="shield-check" class="w-12 h-12 text-slate-300 mb-3"></i>
                            <p>No tax audit documents found. All clear!</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($audits->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $audits->links() }}
    </div>
    @endif
</div>
@endsection
</x-app-layout>
