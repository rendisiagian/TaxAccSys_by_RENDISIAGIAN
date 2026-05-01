<x-app-layout>
@section('title', 'Journal: ' . $journal->voucher_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('journals.index') }}" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="text-xl font-bold text-slate-800 font-mono">{{ $journal->voucher_number }}</h2>
                    @php
                        $statusColors = [
                            'draft' => 'bg-slate-100 text-slate-700',
                            'submitted' => 'bg-amber-100 text-amber-700',
                            'reviewed' => 'bg-blue-100 text-blue-700',
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'rejected' => 'bg-rose-100 text-rose-700',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium uppercase tracking-wider {{ $statusColors[$journal->status] }}">
                        {{ $journal->status }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">{{ $journal->date->format('l, d F Y') }}</p>
            </div>
        </div>

        {{-- Actions based on role and status --}}
        <div class="flex items-center space-x-3">
            @if(auth()->user()->role->slug === 'admin' && $journal->status === 'draft')
                <form action="{{ route('journals.submit', $journal) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium flex items-center">
                        <i data-lucide="send" class="w-4 h-4 mr-2"></i> Submit for Review
                    </button>
                </form>
            @endif

            @if(in_array(auth()->user()->role->slug, ['manager', 'supervisor']) && $journal->status === 'submitted')
                <form action="{{ route('journals.review', $journal) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center">
                        <i data-lucide="check" class="w-4 h-4 mr-2"></i> Mark as Reviewed
                    </button>
                </form>
            @endif

            @if(auth()->user()->role->slug === 'manager' && $journal->status === 'reviewed')
                <form action="{{ route('journals.approve', $journal) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium flex items-center">
                        <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Approve Journal
                    </button>
                </form>
            @endif

            @if(in_array(auth()->user()->role->slug, ['manager', 'supervisor']) && in_array($journal->status, ['submitted', 'reviewed']))
                <button type="button" x-data @click="$dispatch('open-reject-modal')" class="px-4 py-2 bg-white border border-rose-200 text-rose-600 rounded-lg hover:bg-rose-50 transition-colors text-sm font-medium flex items-center">
                    <i data-lucide="x" class="w-4 h-4 mr-2"></i> Reject
                </button>
            @endif

            <button type="button" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium flex items-center" onclick="window.print()">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Journal Header --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Journal Details</h3>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Description</p>
                            <p class="text-sm font-medium text-slate-800">{{ $journal->description }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Reference</p>
                            <p class="text-sm font-medium text-slate-800">{{ $journal->reference ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Branch</p>
                            <p class="text-sm font-medium text-slate-800">{{ $journal->branch->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Project</p>
                            <p class="text-sm font-medium text-slate-800">{{ $journal->project->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                
                {{-- Journal Lines --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border-t border-slate-200">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                <th class="px-6 py-3 font-semibold w-24">Code</th>
                                <th class="px-6 py-3 font-semibold">Account / Memo</th>
                                <th class="px-6 py-3 font-semibold text-right w-40">Debit</th>
                                <th class="px-6 py-3 font-semibold text-right w-40">Credit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($journal->lines as $line)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-mono text-slate-600">{{ $line->account->account_code }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $line->account->account_name }}</div>
                                    @if($line->description)
                                    <div class="text-slate-500 text-xs mt-0.5">{{ $line->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-slate-700">
                                    {{ $line->debit > 0 ? number_format($line->debit, 2, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-slate-700">
                                    {{ $line->credit > 0 ? number_format($line->credit, 2, ',', '.') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200 text-sm font-bold text-slate-800 font-mono">
                            <tr>
                                <td colspan="2" class="px-6 py-4 text-right uppercase tracking-wider text-xs">Total</td>
                                <td class="px-6 py-4 text-right">Rp {{ number_format($journal->total_debit, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right">Rp {{ number_format($journal->total_credit, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Meta Info --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Information</h3>
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Source Module</span>
                        <span class="font-medium text-slate-800 capitalize">{{ str_replace('_', ' ', $journal->source_module) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Created By</span>
                        <span class="font-medium text-slate-800">{{ $journal->createdBy->name ?? 'System' }}</span>
                    </div>
                    @if($journal->reviewed_by)
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Reviewed By</span>
                        <span class="font-medium text-slate-800">{{ $journal->reviewedBy->name }}</span>
                    </div>
                    @endif
                    @if($journal->approved_by)
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Approved By</span>
                        <span class="font-medium text-slate-800">{{ $journal->approvedBy->name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Approval Timeline --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Approval History</h3>
                
                <div class="relative border-l-2 border-slate-200 ml-3 space-y-6">
                    @forelse($journal->approvalLogs as $log)
                    <div class="relative pl-6">
                        @php
                            $logIconColor = match($log->action) {
                                'submitted' => 'bg-amber-100 text-amber-600 border-amber-200',
                                'reviewed' => 'bg-blue-100 text-blue-600 border-blue-200',
                                'approved' => 'bg-emerald-100 text-emerald-600 border-emerald-200',
                                'rejected' => 'bg-rose-100 text-rose-600 border-rose-200',
                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                            };
                            $logIcon = match($log->action) {
                                'submitted' => 'clock',
                                'reviewed' => 'eye',
                                'approved' => 'check',
                                'rejected' => 'x',
                                default => 'activity',
                            };
                        @endphp
                        <span class="absolute -left-[17px] top-1 w-8 h-8 rounded-full border-2 flex items-center justify-center {{ $logIconColor }}">
                            <i data-lucide="{{ $logIcon }}" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ ucfirst($log->action) }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">by {{ $log->user->name }} &middot; {{ $log->created_at->format('d M Y, H:i') }}</p>
                            @if($log->notes)
                            <div class="mt-2 text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded p-2 italic">
                                "{{ $log->notes }}"
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500 pl-4">No history available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div x-data="{ open: false }" @open-reject-modal.window="open = true" class="relative z-50" x-show="open" style="display: none;">
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open" @click.away="open = false" x-transition class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                <form action="{{ route('journals.reject', $journal) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="alert-triangle" class="h-6 w-6 text-rose-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-slate-900">Reject Journal</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500 mb-4">Please provide a reason for rejecting this journal entry. It will be sent back to draft status.</p>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Rejection Reason <span class="text-red-500">*</span></label>
                                    <textarea name="notes" rows="3" required class="w-full rounded-lg border-slate-300 focus:border-rose-500 focus:ring-rose-500 sm:text-sm" placeholder="Reason for rejection..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-200">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-rose-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-700 sm:ml-3 sm:w-auto">Reject</button>
                        <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
</x-app-layout>
