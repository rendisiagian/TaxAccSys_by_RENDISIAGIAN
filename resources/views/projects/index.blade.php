<x-app-layout>
@section('title', __('Projects'))

@section('content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">{{ __('Projects') }}</h2>
            <p class="text-sm text-slate-500 mt-1">Manage construction projects for {{ $company->name }}.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            {{ __('Create') }} {{ __('Project') }}
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 font-semibold">{{ __('Code') }}</th>
                    <th class="px-6 py-4 font-semibold">{{ __('Project') }}</th>
                    <th class="px-6 py-4 font-semibold">Client</th>
                    <th class="px-6 py-4 font-semibold">Contract Value</th>
                    <th class="px-6 py-4 font-semibold">{{ __('Status') }}</th>
                    <th class="px-6 py-4 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm">
                @forelse($projects as $project)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-mono text-slate-600">{{ $project->code }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-800">{{ $project->name }}</div>
                        <div class="text-slate-500 text-xs mt-0.5">{{ $project->location ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-slate-700">{{ $project->client_name ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-slate-800 font-medium">Rp {{ number_format($project->contract_value, 2, ',', '.') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusColors = [
                                'planning' => 'bg-slate-100 text-slate-700',
                                'active' => 'bg-emerald-100 text-emerald-700',
                                'completed' => 'bg-blue-100 text-blue-700',
                                'cancelled' => 'bg-rose-100 text-rose-700',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$project->status] }}">
                            {{ ucfirst($project->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('projects.edit', $project) }}" class="text-slate-400 hover:text-indigo-600 transition-colors" title="{{ __('Edit') }}">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure?') }}');">
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
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        {{ __('No data found.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($projects->hasPages())
    <div class="p-4 border-t border-slate-200">
        {{ $projects->links() }}
    </div>
    @endif
</div>
@endsection
</x-app-layout>
