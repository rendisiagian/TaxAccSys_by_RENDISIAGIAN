<x-app-layout>
@section('title', $company->exists ? __('Edit') . ' ' . __('Company') : __('Create') . ' ' . __('Company'))

@section('content')
<div class="max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">
                {{ $company->exists ? __('Edit') . ' ' . __('Company') : __('Create') . ' ' . __('Company') }}
            </h2>
            @if($company->exists)
            <p class="text-sm text-slate-500 mt-1">{{ $company->name }}</p>
            @endif
        </div>
        <a href="{{ route('companies.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ $company->exists ? route('companies.update', $company) : route('companies.store') }}" method="POST">
            @csrf
            @if($company->exists)
                @method('PUT')
            @endif

            <div class="p-6 space-y-6">
                {{-- Basic Info --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-200 pb-2 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $company->name) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('NPWP') }}</label>
                            <input type="text" name="npwp" value="{{ old('npwp', $company->npwp) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="15 digit NPWP">
                            @error('npwp') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('NITKU') }}</label>
                            <input type="text" name="nitku" value="{{ old('nitku', $company->nitku) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="22 digit NITKU">
                            @error('nitku') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Type') }} <span class="text-red-500">*</span></label>
                            <select name="company_type" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="regular" {{ old('company_type', $company->company_type) === 'regular' ? 'selected' : '' }}>{{ __('Regular') }}</option>
                                <option value="construction" {{ old('company_type', $company->company_type) === 'construction' ? 'selected' : '' }}>{{ __('Construction') }}</option>
                            </select>
                            @error('company_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">KLU Code</label>
                            <input type="text" name="klu_code" value="{{ old('klu_code', $company->klu_code) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('klu_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Business Type</label>
                            <input type="text" name="business_type" value="{{ old('business_type', $company->business_type) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('business_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tax Office (KPP)</label>
                            <input type="text" name="tax_office" value="{{ old('tax_office', $company->tax_office) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('tax_office') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-200 pb-2 mb-4">Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Address') }}</label>
                            <input type="text" name="address" value="{{ old('address', $company->address) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('City') }}</label>
                            <input type="text" name="city" value="{{ old('city', $company->city) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Province') }}</label>
                            <input type="text" name="province" value="{{ old('province', $company->province) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Postal Code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $company->postal_code) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Phone') }}</label>
                            <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Email') }}</label>
                            <input type="email" name="email" value="{{ old('email', $company->email) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                            <textarea name="notes" rows="3" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes', $company->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('companies.index') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
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
