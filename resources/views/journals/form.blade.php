<x-app-layout>
@section('title', 'New Journal Entry')

@section('content')
<div class="max-w-6xl mx-auto" x-data="journalForm()">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">New Journal Entry</h2>
        </div>
        <a href="{{ route('journals.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> {{ __('Back') }}
        </a>
    </div>

    <form action="{{ route('journals.store') }}" method="POST" id="journalForm">
        @csrf
        <input type="hidden" name="action" x-model="submitAction">

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="p-6 border-b border-slate-200 bg-slate-50/50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Voucher Number <span class="text-red-500">*</span></label>
                        <input type="text" name="voucher_number" value="{{ old('voucher_number', $journal->voucher_number) }}" required readonly class="w-full rounded-lg border-slate-300 bg-slate-50 text-slate-500 sm:text-sm focus:ring-0 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" value="{{ old('date', $journal->date->format('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reference (Optional)</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., INV-001">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description <span class="text-red-500">*</span></label>
                        <input type="text" name="description" value="{{ old('description') }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Memo/Header description">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Branch (Optional)</label>
                        <select name="branch_id" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- No Branch --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Project (Optional)</label>
                        <select name="project_id" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- No Project --</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Journal Lines --}}
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="px-4 py-3 font-semibold w-12 text-center">#</th>
                            <th class="px-4 py-3 font-semibold w-64">Account <span class="text-red-500">*</span></th>
                            <th class="px-4 py-3 font-semibold">Line Description</th>
                            <th class="px-4 py-3 font-semibold w-40 text-right">Debit <span class="text-red-500">*</span></th>
                            <th class="px-4 py-3 font-semibold w-40 text-right">Credit <span class="text-red-500">*</span></th>
                            <th class="px-4 py-3 font-semibold w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="lines-container">
                        <template x-for="(line, index) in lines" :key="line.id">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3 text-center text-slate-400 text-sm" x-text="index + 1"></td>
                                <td class="px-4 py-3">
                                    <select x-model="line.account_id" :name="'lines['+index+'][account_id]'" required class="w-full rounded border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5">
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" x-model="line.description" :name="'lines['+index+'][description]'" class="w-full rounded border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5" placeholder="Line memo">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0" x-model.number="line.debit" :name="'lines['+index+'][debit]'" @input="calculateTotals(); if(line.debit > 0) line.credit = 0" class="w-full rounded border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5 text-right font-mono" placeholder="0.00">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0" x-model.number="line.credit" :name="'lines['+index+'][credit]'" @input="calculateTotals(); if(line.credit > 0) line.debit = 0" class="w-full rounded border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5 text-right font-mono" placeholder="0.00">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeLine(index)" x-show="lines.length > 2" class="text-slate-400 hover:text-red-500 transition-colors p-1" title="Remove row">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-slate-50 border-t border-slate-200">
                        <tr>
                            <td colspan="3" class="px-4 py-3">
                                <button type="button" @click="addLine" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add Line
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-800" x-text="formatCurrency(totalDebit)">0.00</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-800" x-text="formatCurrency(totalCredit)">0.00</td>
                            <td></td>
                        </tr>
                        <tr x-show="outOfBalance !== 0" x-transition class="bg-red-50/50">
                            <td colspan="3" class="px-4 py-2 text-right text-sm font-medium text-red-600">Out of Balance:</td>
                            <td colspan="2" class="px-4 py-2 text-center text-sm font-bold font-mono text-red-600" x-text="formatCurrency(Math.abs(outOfBalance))"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 flex justify-between items-center bg-white">
                <div class="text-sm text-slate-500 flex items-center">
                    <i data-lucide="info" class="w-4 h-4 mr-1.5"></i>
                    Journal must be balanced (Debit = Credit) to be saved.
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('journals.index') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
                        Cancel
                    </a>
                    <button type="button" @click="submitForm('draft')" :disabled="outOfBalance !== 0" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        Save as Draft
                    </button>
                    <button type="button" @click="submitForm('submit')" :disabled="outOfBalance !== 0" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="send" class="w-4 h-4 mr-1.5"></i> Submit for Review
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('journalForm', () => ({
            lines: [
                { id: 1, account_id: '', description: '', debit: 0, credit: 0 },
                { id: 2, account_id: '', description: '', debit: 0, credit: 0 }
            ],
            totalDebit: 0,
            totalCredit: 0,
            outOfBalance: 0,
            submitAction: 'draft',
            nextId: 3,

            init() {
                this.$watch('lines', () => this.calculateTotals(), { deep: true });
                // Need to re-init lucide icons when adding rows
                this.$watch('lines', () => {
                    setTimeout(() => lucide.createIcons(), 50);
                });
            },

            addLine() {
                this.lines.push({ id: this.nextId++, account_id: '', description: '', debit: 0, credit: 0 });
            },

            removeLine(index) {
                if (this.lines.length > 2) {
                    this.lines.splice(index, 1);
                    this.calculateTotals();
                }
            },

            calculateTotals() {
                this.totalDebit = this.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
                this.totalCredit = this.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
                
                // Use small epsilon for float comparison
                let diff = this.totalDebit - this.totalCredit;
                this.outOfBalance = Math.abs(diff) < 0.01 ? 0 : diff;
            },

            formatCurrency(value) {
                return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
            },

            submitForm(action) {
                this.submitAction = action;
                this.calculateTotals();
                
                if (this.outOfBalance !== 0) {
                    alert('Journal is not balanced. Debit must equal Credit.');
                    return;
                }
                
                document.getElementById('journalForm').submit();
            }
        }));
    });
</script>
@endpush
@endsection
</x-app-layout>
