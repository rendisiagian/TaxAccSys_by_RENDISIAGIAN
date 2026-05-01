<x-app-layout>
@section('title', $branch->exists ? __('Edit') . ' ' . __('Branch') : __('Create') . ' ' . __('Branch'))

@section('content')
<div class="max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">
                {{ $branch->exists ? __('Edit') . ' ' . __('Branch') : __('Create') . ' ' . __('Branch') }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">{{ $company->name }}</p>
        </div>
        <a href="{{ route('branches.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ $branch->exists ? route('branches.update', $branch) : route('branches.store') }}" method="POST">
            @csrf
            @if($branch->exists)
                @method('PUT')
            @endif

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Code') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $branch->code) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('NITKU') }}</label>
                        <input type="text" name="nitku" value="{{ old('nitku', $branch->nitku) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="22 digit NITKU">
                        @error('nitku') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Address') }}</label>
                        <input type="text" name="address" value="{{ old('address', $branch->address) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('City') }}</label>
                        <input type="text" name="city" value="{{ old('city', $branch->city) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Province') }}</label>
                        <input type="text" name="province" value="{{ old('province', $branch->province) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Phone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div class="col-span-2 pt-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_head_office" value="1" {{ old('is_head_office', $branch->is_head_office) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-slate-700 font-medium">{{ __('Head Office') }}</span>
                        </label>
                        <p class="ml-6 text-xs text-slate-500 mt-1">Check this if this branch serves as the head office. Only one branch can be head office.</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('branches.index') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
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
