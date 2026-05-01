<x-app-layout>
@section('title', __('Branches'))

@section('content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">{{ __('Branches') }}</h2>
            <p class="text-sm text-slate-500 mt-1">Manage branches for {{ $company->name }}.</p>
        </div>
        <a href="{{ route('branches.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            {{ __('Create') }} {{ __('Branch') }}
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 font-semibold">{{ __('Code') }}</th>
                    <th class="px-6 py-4 font-semibold">{{ __('Branch') }}</th>
                    <th class="px-6 py-4 font-semibold">{{ __('NITKU') }}</th>
                    <th class="px-6 py-4 font-semibold">{{ __('Status') }}</th>
                    <th class="px-6 py-4 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm">
                @forelse($branches as $branch)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-mono text-slate-600">{{ $branch->code }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-800 flex items-center">
                            {{ $branch->name }}
                            @if($branch->is_head_office)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-700 uppercase tracking-wider">
                                HO
                            </span>
                            @endif
                        </div>
                        <div class="text-slate-500 text-xs mt-0.5">{{ $branch->city ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-slate-700">{{ $branch->nitku ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($branch->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            {{ __('Active') }}
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                            {{ __('Inactive') }}
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('branches.edit', $branch) }}" class="text-slate-400 hover:text-indigo-600 transition-colors" title="{{ __('Edit') }}">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors" title="{{ __('Delete') }}">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        {{ __('No data found.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($branches->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $branches->links() }}
    </div>
    @endif
</div>
@endsection
</x-app-layout>
