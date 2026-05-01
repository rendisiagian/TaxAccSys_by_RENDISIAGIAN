<x-app-layout>
@section('title', __('Journal Entries'))

@section('content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">{{ __('Journal Entries') }}</h2>
            <p class="text-sm text-slate-500 mt-1">Manage manual and automated journal entries.</p>
        </div>
        <a href="{{ route('journals.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            New Journal
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 font-semibold">Date & Voucher</th>
                    <th class="px-6 py-4 font-semibold">{{ __('Description') }}</th>
                    <th class="px-6 py-4 font-semibold text-right">Amount</th>
                    <th class="px-6 py-4 font-semibold">{{ __('Status') }}</th>
                    <th class="px-6 py-4 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm">
                @forelse($journals as $journal)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-800">{{ $journal->date->format('d M Y') }}</div>
                        <div class="text-slate-500 text-xs mt-0.5 font-mono">{{ $journal->voucher_number }}</div>
                    </td>
                    <td class="px-6 py-4 max-w-md">
                        <div class="text-slate-700 truncate" title="{{ $journal->description }}">{{ $journal->description }}</div>
                        <div class="text-slate-400 text-xs mt-0.5 flex items-center space-x-2">
                            @if($journal->branch_id)
                                <span><i data-lucide="map-pin" class="w-3 h-3 inline mr-0.5"></i> {{ $journal->branch->name }}</span>
                            @endif
                            @if($journal->project_id)
                                <span><i data-lucide="folder-kanban" class="w-3 h-3 inline mr-0.5"></i> {{ $journal->project->name }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="font-medium text-slate-800">Rp {{ number_format($journal->total_debit, 2, ',', '.') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusColors = [
                                'draft' => 'bg-slate-100 text-slate-700',
                                'submitted' => 'bg-amber-100 text-amber-700',
                                'reviewed' => 'bg-blue-100 text-blue-700',
                                'approved' => 'bg-emerald-100 text-emerald-700',
                                'rejected' => 'bg-rose-100 text-rose-700',
                            ];
                            $statusIcons = [
                                'draft' => 'edit-3',
                                'submitted' => 'clock',
                                'reviewed' => 'eye',
                                'approved' => 'check-circle',
                                'rejected' => 'x-circle',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium {{ $statusColors[$journal->status] }}">
                            <i data-lucide="{{ $statusIcons[$journal->status] }}" class="w-3.5 h-3.5 mr-1.5"></i>
                            {{ ucfirst($journal->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('journals.show', $journal) }}" class="text-slate-400 hover:text-indigo-600 transition-colors" title="View">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            
                            @if($journal->status !== 'approved')
                            <form action="{{ route('journals.destroy', $journal) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors" title="{{ __('Delete') }}">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="file-text" class="w-12 h-12 text-slate-300 mb-3"></i>
                            <p>No journal entries found.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($journals->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $journals->links() }}
    </div>
    @endif
</div>
@endsection
</x-app-layout>
