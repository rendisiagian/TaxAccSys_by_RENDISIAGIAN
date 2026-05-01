<x-app-layout>
@section('title', __('Dashboard'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Executive Dashboard</h2>
            <p class="text-sm text-slate-500 mt-1">Overview of tax liabilities and recent activities for {{ request()->user()->currentCompany->name }}.</p>
        </div>
    </div>

    @if($unresolvedAudits->count() > 0)
    <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i data-lucide="shield-alert" class="h-5 w-5 text-rose-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-rose-800">Action Required: Tax Audits</h3>
                <div class="mt-2 text-sm text-rose-700">
                    <p>You have {{ $unresolvedAudits->count() }} unresolved tax audit document(s) that need attention.</p>
                </div>
                <div class="mt-4">
                    <div class="-mx-2 -my-1.5 flex flex-wrap gap-2">
                        @foreach($unresolvedAudits->take(3) as $audit)
                        <a href="{{ route('tax-audits.edit', $audit) }}" class="rounded-md bg-rose-100 px-2 py-1.5 text-xs font-medium text-rose-800 hover:bg-rose-200 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 focus:ring-offset-rose-50">
                            {{ $audit->document_type }} - {{ $audit->document_number }}
                        </a>
                        @endforeach
                        @if($unresolvedAudits->count() > 3)
                        <a href="{{ route('tax-audits.index') }}" class="rounded-md bg-rose-100 px-2 py-1.5 text-xs font-medium text-rose-800 hover:bg-rose-200">
                            + {{ $unresolvedAudits->count() - 3 }} more
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <p class="text-sm font-medium text-slate-500 mb-1">PPN Masukan (In)</p>
                <h3 class="text-2xl font-bold text-slate-800 font-mono">Rp {{ number_format($ppnIn, 0, ',', '.') }}</h3>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <span class="inline-flex items-center text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded mr-2">
                        <i data-lucide="trending-up" class="w-3 h-3 mr-1"></i> Creditable
                    </span>
                    Total VAT paid on purchases
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <p class="text-sm font-medium text-slate-500 mb-1">PPN Keluaran (Out)</p>
                <h3 class="text-2xl font-bold text-slate-800 font-mono">Rp {{ number_format($ppnOut, 0, ',', '.') }}</h3>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <span class="inline-flex items-center text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded mr-2">
                        <i data-lucide="trending-down" class="w-3 h-3 mr-1"></i> Payable
                    </span>
                    Total VAT collected from sales
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <p class="text-sm font-medium text-slate-500 mb-1">Total PPh 21 Payable</p>
                <h3 class="text-2xl font-bold text-slate-800 font-mono">Rp {{ number_format($pph21, 0, ',', '.') }}</h3>
                <div class="mt-4 flex items-center text-xs text-slate-500">
                    <i data-lucide="users" class="w-4 h-4 mr-1 text-indigo-500"></i> Based on generated tax journals
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2">VAT Overview</h3>
            <div class="relative h-64 w-full">
                <canvas id="vatChart"></canvas>
            </div>
            @if($ppnOut - $ppnIn > 0)
            <div class="mt-4 text-sm text-center text-slate-600 bg-slate-50 py-2 rounded-lg border border-slate-200">
                Net VAT Payable (Kurang Bayar): <span class="font-bold text-rose-600 font-mono">Rp {{ number_format($ppnOut - $ppnIn, 0, ',', '.') }}</span>
            </div>
            @else
            <div class="mt-4 text-sm text-center text-slate-600 bg-slate-50 py-2 rounded-lg border border-slate-200">
                Net VAT Overpaid (Lebih Bayar): <span class="font-bold text-emerald-600 font-mono">Rp {{ number_format(abs($ppnOut - $ppnIn), 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2">Tax Compliance Status</h3>
            <div class="relative h-64 w-full flex items-center justify-center">
                <div class="text-center text-slate-500">
                    <i data-lucide="activity" class="w-12 h-12 text-slate-200 mx-auto mb-3"></i>
                    <p class="text-sm">Historical tax payment data will be visualized here once sufficient Journal Entries are populated.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('vatChart');
        
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Current Period'],
                    datasets: [
                        {
                            label: 'PPN Masukan (In)',
                            data: [{{ $ppnIn }}],
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgb(37, 99, 235)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'PPN Keluaran (Out)',
                            data: [{{ $ppnOut }}],
                            backgroundColor: 'rgba(244, 63, 94, 0.8)',
                            borderColor: 'rgb(225, 29, 72)',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return 'Rp ' + (value / 1000000) + 'M';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
</x-app-layout>
