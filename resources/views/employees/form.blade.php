<x-app-layout>
@section('title', $employee->exists ? __('Edit') . ' Employee' : __('Create') . ' Employee')

@section('content')
<div class="max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">
                {{ $employee->exists ? __('Edit') . ' Employee' : __('Create') . ' Employee' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">Manage employee tax profile</p>
        </div>
        <a href="{{ route('employees.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ $employee->exists ? route('employees.update', $employee) : route('employees.store') }}" method="POST">
            @csrf
            @if($employee->exists)
                @method('PUT')
            @endif

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $employee->name) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">NIK (KTP)</label>
                        <input type="text" name="nik" value="{{ old('nik', $employee->nik) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono">
                        @error('nik') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">NPWP</label>
                        <input type="text" name="npwp" value="{{ old('npwp', $employee->npwp) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono">
                        @error('npwp') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">NITKU</label>
                        <input type="text" name="nitku" value="{{ old('nitku', $employee->nitku) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Branch (Optional)</label>
                        <select name="branch_id" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- No Branch --</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 border-t border-slate-200 pt-6">
                        <h4 class="text-sm font-semibold text-slate-800 mb-4">Tax Information (PPh 21)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Employee Type <span class="text-red-500">*</span></label>
                                <select name="employee_type" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="tetap" {{ old('employee_type', $employee->employee_type) === 'tetap' ? 'selected' : '' }}>Pegawai Tetap</option>
                                    <option value="tidak_tetap" {{ old('employee_type', $employee->employee_type) === 'tidak_tetap' ? 'selected' : '' }}>Pegawai Tidak Tetap</option>
                                    <option value="bukan_pegawai" {{ old('employee_type', $employee->employee_type) === 'bukan_pegawai' ? 'selected' : '' }}>Bukan Pegawai</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">PTKP Status <span class="text-red-500">*</span></label>
                                <select name="ptkp_status" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <optgroup label="TER Category A">
                                        <option value="TK/0" {{ old('ptkp_status', $employee->ptkp_status) === 'TK/0' ? 'selected' : '' }}>TK/0 (Tidak Kawin, 0 Tanggungan)</option>
                                        <option value="TK/1" {{ old('ptkp_status', $employee->ptkp_status) === 'TK/1' ? 'selected' : '' }}>TK/1</option>
                                        <option value="K/0" {{ old('ptkp_status', $employee->ptkp_status) === 'K/0' ? 'selected' : '' }}>K/0</option>
                                    </optgroup>
                                    <optgroup label="TER Category B">
                                        <option value="TK/2" {{ old('ptkp_status', $employee->ptkp_status) === 'TK/2' ? 'selected' : '' }}>TK/2</option>
                                        <option value="TK/3" {{ old('ptkp_status', $employee->ptkp_status) === 'TK/3' ? 'selected' : '' }}>TK/3</option>
                                        <option value="K/1" {{ old('ptkp_status', $employee->ptkp_status) === 'K/1' ? 'selected' : '' }}>K/1</option>
                                        <option value="K/2" {{ old('ptkp_status', $employee->ptkp_status) === 'K/2' ? 'selected' : '' }}>K/2</option>
                                    </optgroup>
                                    <optgroup label="TER Category C">
                                        <option value="K/3" {{ old('ptkp_status', $employee->ptkp_status) === 'K/3' ? 'selected' : '' }}>K/3 (Kawin, 3 Tanggungan)</option>
                                    </optgroup>
                                </select>
                                <p class="text-xs text-slate-500 mt-2">
                                    <strong>Note:</strong> TER Category (A, B, C) is assigned based on PTKP status. The actual <strong>TER Rate (%)</strong> will be calculated dynamically during the monthly tax process based on this Category <em>and</em> the employee's Gross Income (Bruto).
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 pt-2">
                        <label class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-slate-700 font-medium">Active Employee</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('employees.index') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
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
