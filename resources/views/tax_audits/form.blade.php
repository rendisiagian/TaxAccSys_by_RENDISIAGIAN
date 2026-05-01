<x-app-layout>
@section('title', $audit->exists ? 'Edit Document' : 'Record Document')

@section('content')
<div class="max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">
                {{ $audit->exists ? 'Edit Tax Document' : 'Record Tax Document' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">SP2DK, STP, SKPKB, SKPLB, SKPN</p>
        </div>
        <a href="{{ route('tax-audits.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ $audit->exists ? route('tax-audits.update', $audit) : route('tax-audits.store') }}" method="POST">
            @csrf
            @if($audit->exists)
                @method('PUT')
            @endif

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Document Type <span class="text-red-500">*</span></label>
                        <select name="document_type" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-bold">
                            <option value="SP2DK" {{ old('document_type', $audit->document_type) === 'SP2DK' ? 'selected' : '' }}>SP2DK (Surat Permintaan Penjelasan Data/Keterangan)</option>
                            <option value="STP" {{ old('document_type', $audit->document_type) === 'STP' ? 'selected' : '' }}>STP (Surat Tagihan Pajak)</option>
                            <option value="SKPKB" {{ old('document_type', $audit->document_type) === 'SKPKB' ? 'selected' : '' }}>SKPKB (Surat Ketetapan Pajak Kurang Bayar)</option>
                            <option value="SKPLB" {{ old('document_type', $audit->document_type) === 'SKPLB' ? 'selected' : '' }}>SKPLB (Surat Ketetapan Pajak Lebih Bayar)</option>
                            <option value="SKPN" {{ old('document_type', $audit->document_type) === 'SKPN' ? 'selected' : '' }}>SKPN (Surat Ketetapan Pajak Nihil)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Document Date <span class="text-red-500">*</span></label>
                        <input type="date" name="document_date" value="{{ old('document_date', $audit->document_date ? $audit->document_date->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Document Number <span class="text-red-500">*</span></label>
                        <input type="text" name="document_number" value="{{ old('document_number', $audit->document_number) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono" placeholder="S-123/KPP.01/2026">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tax Period Year <span class="text-red-500">*</span></label>
                        <input type="number" name="tax_period_year" value="{{ old('tax_period_year', $audit->tax_period_year) }}" required min="2000" max="2100" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="text-[10px] text-slate-500 mt-1">Year of the tax return being audited.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="received" {{ old('status', $audit->status) === 'received' ? 'selected' : '' }}>Received (Unresolved)</option>
                            <option value="responded" {{ old('status', $audit->status) === 'responded' ? 'selected' : '' }}>Responded (In Progress)</option>
                            <option value="closed" {{ old('status', $audit->status) === 'closed' ? 'selected' : '' }}>Closed (Resolved)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-slate-100">
                        <h4 class="text-sm font-semibold text-slate-800 mb-4">Values (If Applicable)</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Principal Amount (Pokok Pajak)</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-slate-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" step="0.01" name="principal_amount" value="{{ old('principal_amount', $audit->principal_amount) }}" class="block w-full rounded-lg border-slate-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono text-right" placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Penalty Amount (Sanksi/Denda)</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-slate-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" step="0.01" name="penalty_amount" value="{{ old('penalty_amount', $audit->penalty_amount) }}" class="block w-full rounded-lg border-slate-300 pl-10 focus:border-rose-500 focus:ring-rose-500 sm:text-sm font-mono text-right" placeholder="0.00">
                        </div>
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-slate-100">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes / Follow-up Actions</label>
                        <textarea name="notes" rows="3" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Detail any responses sent or required actions here...">{{ old('notes', $audit->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('tax-audits.index') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                    {{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
</x-app-layout>
