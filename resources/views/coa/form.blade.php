<x-app-layout>
@section('title', $account->exists ? __('Edit') . ' Account' : __('Create') . ' Account')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">
                {{ $account->exists ? __('Edit') . ' Account' : __('Create') . ' Account' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">{{ $company->name }}</p>
        </div>
        <a href="{{ route('coa.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ $account->exists ? route('coa.update', $account) : route('coa.store') }}" method="POST">
            @csrf
            @if($account->exists)
                @method('PUT')
            @endif

            <div class="p-6 space-y-5">
                @if($account->is_system)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4 flex items-start">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 mt-0.5 mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-amber-800">System Account</h4>
                        <p class="text-xs text-amber-700 mt-1">This is a system required account. Some fields may be restricted, and it cannot be deleted.</p>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Code') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="account_code" value="{{ old('account_code', $account->account_code) }}" required {{ $account->is_system ? 'readonly' : '' }} class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $account->is_system ? 'bg-slate-50 text-slate-500' : '' }}">
                        @error('account_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="account_name" value="{{ old('account_name', $account->account_name) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('account_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Type') }} <span class="text-red-500">*</span></label>
                        <select name="account_type" required {{ $account->is_system ? 'disabled' : '' }} class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $account->is_system ? 'bg-slate-50 text-slate-500' : '' }}">
                            @foreach(['asset', 'liability', 'equity', 'revenue', 'cogs', 'expense', 'other_income', 'tax'] as $type)
                            <option value="{{ $type }}" {{ old('account_type', $account->account_type) === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                        @if($account->is_system)
                            <input type="hidden" name="account_type" value="{{ $account->account_type }}">
                        @endif
                        @error('account_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Normal Balance <span class="text-red-500">*</span></label>
                        <select name="normal_balance" required {{ $account->is_system ? 'disabled' : '' }} class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $account->is_system ? 'bg-slate-50 text-slate-500' : '' }}">
                            <option value="debit" {{ old('normal_balance', $account->normal_balance) === 'debit' ? 'selected' : '' }}>Debit</option>
                            <option value="credit" {{ old('normal_balance', $account->normal_balance) === 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                        @if($account->is_system)
                            <input type="hidden" name="normal_balance" value="{{ $account->normal_balance }}">
                        @endif
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Parent Account</label>
                        <select name="parent_id" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- None (Top Level) --</option>
                            @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $account->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->account_code }} - {{ $parent->account_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('parent_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-2 pt-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_header" value="1" {{ old('is_header', $account->is_header) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-slate-700 font-medium">Is Header Account</span>
                        </label>
                        <p class="ml-6 text-xs text-slate-500 mt-1">Header accounts group other accounts and cannot receive journal postings directly.</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('coa.index') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
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
