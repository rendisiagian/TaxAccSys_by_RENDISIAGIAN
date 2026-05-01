<x-app-layout>
@section('title', 'Record Unifikasi Transaction')

@section('content')
<div class="max-w-3xl" x-data="taxCalculator()">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Record Unifikasi Transaction</h2>
            <p class="text-sm text-slate-500 mt-1">Input Bukti Potong PPh 22, 23/26, 4(2)</p>
        </div>
        <a href="{{ route('taxes.unifikasi.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ route('taxes.transactions.store') }}" method="POST">
            @csrf
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tax Type <span class="text-red-500">*</span></label>
                        <select name="tax_type" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="pph_23">PPh Pasal 23 (Jasa/Sewa)</option>
                            <option value="pph_4_2">PPh Pasal 4 ayat (2) (Sewa Tanah/Bangunan)</option>
                            <option value="pph_22">PPh Pasal 22</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Transaction Date <span class="text-red-500">*</span></label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Document Number (Bukti Potong) <span class="text-red-500">*</span></label>
                        <input type="text" name="document_number" value="{{ old('document_number') }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., 3.2-1234567">
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-slate-100">
                        <h4 class="text-sm font-semibold text-slate-800 mb-4">Counterpart (Lawan Transaksi)</h4>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select Client / Vendor (Optional)</label>
                        <select @change="selectCounterpart($event.target.value)" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- Manual Entry --</option>
                            <optgroup label="Vendors">
                                @foreach($vendors as $v)
                                    <option value="{{ json_encode(['name' => $v->name, 'tin' => $v->tin]) }}">{{ $v->name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Clients">
                                @foreach($clients as $c)
                                    <option value="{{ json_encode(['name' => $c->name, 'tin' => $c->tin]) }}">{{ $c->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">Selecting from the list will auto-fill the Name and NPWP below.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="counterpart_name" x-model="counterpartName" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">NPWP <span class="text-red-500">*</span></label>
                        <input type="text" name="counterpart_tin" x-model="counterpartTin" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono">
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-slate-100">
                        <h4 class="text-sm font-semibold text-slate-800 mb-4">Calculation</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tax Base (DPP) <span class="text-red-500">*</span></label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-slate-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" step="0.01" name="tax_base" x-model.number="taxBase" @input="calculateTax" required class="block w-full rounded-lg border-slate-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono text-right" placeholder="0.00">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Rate (%) <span class="text-red-500">*</span></label>
                            <div class="relative rounded-md shadow-sm">
                                <input type="number" step="0.01" name="tax_rate" x-model.number="taxRate" @input="calculateTax" required class="block w-full rounded-lg border-slate-300 pr-8 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-right">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-slate-500 sm:text-sm">%</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tax Amount <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="tax_amount" x-model="taxAmount" required readonly class="w-full rounded-lg border-slate-300 bg-slate-50 text-slate-500 focus:ring-0 sm:text-sm text-right font-mono font-bold">
                        </div>
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-slate-100">
                        <div class="grid grid-cols-2 gap-6">
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
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('taxes.unifikasi.index') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                    Save Record
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('taxCalculator', () => ({
            taxBase: 0,
            taxRate: 2, // Default 2%
            taxAmount: 0,
            counterpartName: '{{ old('counterpart_name') }}',
            counterpartTin: '{{ old('counterpart_tin') }}',

            selectCounterpart(jsonVal) {
                if(!jsonVal) {
                    this.counterpartName = '';
                    this.counterpartTin = '';
                    return;
                }
                try {
                    let data = JSON.parse(jsonVal);
                    this.counterpartName = data.name || '';
                    this.counterpartTin = data.tin || '';
                } catch(e) {}
            },

            calculateTax() {
                let base = parseFloat(this.taxBase) || 0;
                let rate = parseFloat(this.taxRate) || 0;
                this.taxAmount = (base * (rate / 100)).toFixed(2);
            }
        }));
    });
</script>
@endpush
@endsection
</x-app-layout>
