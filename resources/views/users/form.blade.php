<x-app-layout>
@section('title', $user->exists ? __('Edit') . ' ' . __('User') : __('Create') . ' ' . __('User'))

@section('content')
<div class="max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800">
                {{ $user->exists ? __('Edit') . ' ' . __('User') : __('Create') . ' ' . __('User') }}
            </h2>
        </div>
        <a href="{{ route('users.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST">
            @csrf
            @if($user->exists)
                @method('PUT')
            @endif

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Left Col: User Data --}}
                    <div class="space-y-5">
                        <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-200 pb-2">User Details</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Email') }} <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Role') }} <span class="text-red-500">*</span></label>
                            <select name="role_id" required class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Language</label>
                                <select name="locale" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="id" {{ old('locale', $user->locale) === 'id' ? 'selected' : '' }}>Indonesia (ID)</option>
                                    <option value="en" {{ old('locale', $user->locale) === 'en' ? 'selected' : '' }}>English (EN)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Status') }}</label>
                                <select name="is_active" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="1" {{ old('is_active', $user->is_active ?? true) ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $user->is_active ?? true) ? '' : 'selected' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ $user->exists ? 'Reset Password (leave blank to keep current)' : 'Password *' }}
                            </label>
                            <input type="password" name="password" {{ $user->exists ? '' : 'required' }} class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    {{-- Right Col: Company Access --}}
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-200 pb-2">Company Access</h3>
                        <p class="text-xs text-slate-500 mb-2">Select the companies this user can access.</p>
                        
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 max-h-96 overflow-y-auto space-y-2">
                            @php
                                $userCompanies = $user->exists ? $user->companies->pluck('id')->toArray() : [];
                                $oldCompanies = old('companies', $userCompanies);
                            @endphp
                            
                            @foreach($companies as $company)
                            <label class="flex items-start p-2 hover:bg-white rounded border border-transparent hover:border-slate-200 cursor-pointer transition-colors">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="companies[]" value="{{ $company->id }}" 
                                        {{ in_array($company->id, $oldCompanies) ? 'checked' : '' }}
                                        class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-slate-800">{{ $company->name }}</span>
                                    <p class="text-slate-500 text-xs">{{ $company->npwp ?? 'No NPWP' }}</p>
                                </div>
                            </label>
                            @endforeach
                            @error('companies') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('users.index') }}" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium">
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
