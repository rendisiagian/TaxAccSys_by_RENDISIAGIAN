<x-app-layout>
@section('title', __('Chart of Accounts'))

@section('content')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-[calc(100vh-140px)]">
    <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">{{ __('Chart of Accounts') }}</h2>
            <p class="text-sm text-slate-500 mt-1">Manage accounts for {{ $company->name }}.</p>
        </div>
        <a href="{{ route('coa.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            {{ __('Create') }} Account
        </a>
    </div>

    <div class="flex-1 overflow-y-auto p-6 bg-slate-50">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 divide-y divide-slate-100">
                <div class="grid grid-cols-12 gap-4 p-4 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50 rounded-t-lg">
                    <div class="col-span-4">{{ __('Code') }} & {{ __('Name') }}</div>
                    <div class="col-span-2">{{ __('Type') }}</div>
                    <div class="col-span-2">Normal Bal.</div>
                    <div class="col-span-2">{{ __('Status') }}</div>
                    <div class="col-span-2 text-right">{{ __('Actions') }}</div>
                </div>

                @php
                    function renderAccount($account, $depth = 0) {
                        $padding = $depth * 1.5;
                        $isHeader = $account->is_header;
                        
                        $typeColors = [
                            'asset' => 'text-blue-600 bg-blue-50',
                            'liability' => 'text-rose-600 bg-rose-50',
                            'equity' => 'text-purple-600 bg-purple-50',
                            'revenue' => 'text-emerald-600 bg-emerald-50',
                            'cogs' => 'text-orange-600 bg-orange-50',
                            'expense' => 'text-amber-600 bg-amber-50',
                            'other_income' => 'text-cyan-600 bg-cyan-50',
                            'tax' => 'text-indigo-600 bg-indigo-50',
                        ];
                        
                        $typeColor = $typeColors[$account->account_type] ?? 'text-slate-600 bg-slate-50';
                        
                        $html = '<div class="grid grid-cols-12 gap-4 p-3 hover:bg-slate-50 transition-colors items-center ' . ($isHeader ? 'bg-slate-50/50' : '') . '">';
                        
                        // Name and Code
                        $html .= '<div class="col-span-4 flex items-center" style="padding-left: ' . $padding . 'rem">';
                        if ($isHeader) {
                            $html .= '<i data-lucide="folder" class="w-4 h-4 text-slate-400 mr-2"></i>';
                            $html .= '<span class="font-semibold text-slate-800">' . $account->account_code . ' - ' . $account->account_name . '</span>';
                        } else {
                            $html .= '<i data-lucide="file-text" class="w-4 h-4 text-slate-300 mr-2"></i>';
                            $html .= '<span class="text-slate-600"><span class="font-medium mr-1">' . $account->account_code . '</span> ' . $account->account_name . '</span>';
                        }
                        if ($account->is_system) {
                            $html .= '<i data-lucide="lock" class="w-3 h-3 text-slate-300 ml-2" title="System Account"></i>';
                        }
                        $html .= '</div>';
                        
                        // Type
                        $html .= '<div class="col-span-2">';
                        $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider ' . $typeColor . '">' . $account->account_type . '</span>';
                        $html .= '</div>';
                        
                        // Balance
                        $html .= '<div class="col-span-2">';
                        $html .= '<span class="text-xs text-slate-500 capitalize">' . $account->normal_balance . '</span>';
                        $html .= '</div>';
                        
                        // Status
                        $html .= '<div class="col-span-2">';
                        if ($account->is_active) {
                            $html .= '<span class="inline-flex items-center text-xs font-medium text-emerald-600"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>' . __('Active') . '</span>';
                        } else {
                            $html .= '<span class="text-xs text-slate-400">' . __('Inactive') . '</span>';
                        }
                        $html .= '</div>';
                        
                        // Actions
                        $html .= '<div class="col-span-2 flex items-center justify-end space-x-2">';
                        $html .= '<a href="' . route('coa.edit', $account) . '" class="p-1 text-slate-400 hover:text-indigo-600 transition-colors" title="' . __('Edit') . '"><i data-lucide="edit-2" class="w-4 h-4"></i></a>';
                        if (!$account->is_system && $account->children->count() === 0) {
                            $html .= '<form action="' . route('coa.destroy', $account) . '" method="POST" class="inline-block" onsubmit="return confirm(\'' . __('Are you sure?') . '\');">';
                            $html .= csrf_field() . method_field('DELETE');
                            $html .= '<button type="submit" class="p-1 text-slate-400 hover:text-red-600 transition-colors" title="' . __('Delete') . '"><i data-lucide="trash-2" class="w-4 h-4"></i></button>';
                            $html .= '</form>';
                        } else {
                            $html .= '<span class="p-1 w-6 h-6 inline-block"></span>'; // Spacer
                        }
                        $html .= '</div>';
                        
                        $html .= '</div>';
                        
                        foreach ($account->children as $child) {
                            $html .= renderAccount($child, $depth + 1);
                        }
                        
                        return $html;
                    }
                @endphp

                <div class="divide-y divide-slate-100 text-sm">
                    @foreach($accounts as $account)
                        {!! renderAccount($account) !!}
                    @endforeach
                    
                    @if($accounts->isEmpty())
                    <div class="p-8 text-center text-slate-500">
                        {{ __('No data found.') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
</x-app-layout>
