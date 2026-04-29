@extends('layouts.app')

@section('title', \App\Helpers\Helpers::translate('dashboard'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">{{ \App\Helpers\Helpers::translate('dashboard') }}</h2>
        <span class="text-muted small">{{ now()->format('d M Y') }}</span>
    </div>

    {{-- ── Row 1: Inventory & People ──────────────────────────────────────── --}}
    <div class="row g-4 mb-4">

        {{-- Total Products --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('admin.dash_total_products') }}</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_products']) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background-color:rgba(79,70,229,.12);">
                            <i class="bi bi-capsule fs-4" style="color:var(--primary);"></i>
                        </div>
                    </div>
                    <span class="text-muted small">
                        {{ __('admin.dash_active_products') }}:
                        <strong class="text-success">{{ number_format($stats['active_products']) }}</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- Total Pharmacies --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('admin.dash_total_pharmacies') }}</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_pharmacies']) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background-color:rgba(16,185,129,.12);">
                            <i class="bi bi-hospital fs-4" style="color:var(--success);"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.pharmacies.index') }}" class="text-muted small text-decoration-none">
                        {{ \App\Helpers\Helpers::translate('view_all') }} &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Sales Reps --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('admin.dash_total_reps') }}</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_reps']) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background-color:rgba(59,130,246,.12);">
                            <i class="bi bi-people fs-4" style="color:var(--info);"></i>
                        </div>
                    </div>
                    <span class="text-muted small">{{ \App\Helpers\Helpers::translate('rep') }}</span>
                </div>
            </div>
        </div>

        {{-- Low Stock Alert --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100 {{ $stats['low_stock_count'] > 0 ? 'border-warning border' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('admin.dash_low_stock') }}</h6>
                            <h3 class="fw-bold mb-0 {{ $stats['low_stock_count'] > 0 ? 'text-warning' : 'text-success' }}">
                                {{ number_format($stats['low_stock_count']) }}
                            </h3>
                        </div>
                        <div class="rounded-circle p-2" style="background-color:rgba(245,158,11,.12);">
                            <i class="bi bi-exclamation-triangle fs-4" style="color:var(--warning);"></i>
                        </div>
                    </div>
                    <span class="text-muted small">{{ __('admin.dash_low_stock_list') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Order & Financial Stats ─────────────────────────────────── --}}
    <div class="row g-4 mb-4">

        {{-- Orders Today --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('admin.dash_orders_today') }}</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['orders_today']) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background-color:rgba(79,70,229,.12);">
                            <i class="bi bi-receipt fs-4" style="color:var(--primary);"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="text-muted small text-decoration-none">
                        {{ \App\Helpers\Helpers::translate('view_all') }} &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Pending Orders --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100 {{ $stats['pending_orders'] > 0 ? 'border-warning border' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('admin.dash_pending_orders') }}</h6>
                            <h3 class="fw-bold mb-0 {{ $stats['pending_orders'] > 0 ? 'text-warning' : '' }}">
                                {{ number_format($stats['pending_orders']) }}
                            </h3>
                        </div>
                        <div class="rounded-circle p-2" style="background-color:rgba(245,158,11,.12);">
                            <i class="bi bi-hourglass-split fs-4" style="color:var(--warning);"></i>
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark">{{ __('admin.order_status.pending') }}</span>
                </div>
            </div>
        </div>

        {{-- Sales This Month --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('admin.dash_sales_month') }}</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['sales_month'], 0) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background-color:rgba(16,185,129,.12);">
                            <i class="bi bi-graph-up-arrow fs-4" style="color:var(--success);"></i>
                        </div>
                    </div>
                    <span class="text-muted small">{{ __('admin.dash_confirmed_month') }}: <strong>{{ number_format($stats['confirmed_month']) }}</strong></span>
                </div>
            </div>
        </div>

        {{-- Payments This Month --}}
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">{{ __('admin.dash_payments_month') }}</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['payments_month'], 0) }}</h3>
                        </div>
                        <div class="rounded-circle p-2" style="background-color:rgba(59,130,246,.12);">
                            <i class="bi bi-cash-coin fs-4" style="color:var(--info);"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.payments.index') }}" class="text-muted small text-decoration-none">
                        {{ \App\Helpers\Helpers::translate('view_all') }} &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 3: Recent Lists ──────────────────────────────────────────────── --}}
    <div class="row g-4">

        {{-- Latest Orders --}}
        <div class="col-lg-4">
            <div class="card border-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>{{ __('admin.dash_recent_orders') }}</h6>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">
                        {{ \App\Helpers\Helpers::translate('view_all') }}
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ \App\Helpers\Helpers::translate('order_number') }}</th>
                                    <th>{{ \App\Helpers\Helpers::translate('pharmacy') }}</th>
                                    <th>{{ \App\Helpers\Helpers::translate('total') }}</th>
                                    <th>{{ \App\Helpers\Helpers::translate('status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $ord)
                                @php $sc = ['draft'=>'secondary','pending'=>'warning','confirmed'=>'success','cancelled'=>'danger']; @endphp
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('admin.orders.show', $ord) }}" class="text-decoration-none fw-medium">
                                            {{ $ord->order_number ?? '#'.$ord->id }}
                                        </a>
                                    </td>
                                    <td class="text-truncate" style="max-width:110px;">{{ $ord->pharmacy?->name ?? '-' }}</td>
                                    <td>{{ number_format($ord->total, 0) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $sc[$ord->status] ?? 'secondary' }}">
                                            {{ __('admin.order_status.' . $ord->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        {{ \App\Helpers\Helpers::translate('no_records_found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Latest Payments --}}
        <div class="col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-cash-coin me-2"></i>{{ __('admin.dash_recent_payments') }}</h6>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">
                        {{ \App\Helpers\Helpers::translate('view_all') }}
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ \App\Helpers\Helpers::translate('pharmacy') }}</th>
                                    <th>{{ __('admin.payment_amount') }}</th>
                                    <th>{{ __('admin.payment_paid_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $pay)
                                <tr>
                                    <td class="ps-3 text-truncate" style="max-width:120px;">
                                        {{ $pay->pharmacy?->name ?? '-' }}
                                    </td>
                                    <td class="fw-medium text-success">{{ number_format($pay->amount, 0) }}</td>
                                    <td>{{ $pay->paid_at?->format('d M') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        {{ \App\Helpers\Helpers::translate('no_records_found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Low Stock Alert --}}
        <div class="col-lg-2">
            <div class="card border-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ __('admin.dash_low_stock_list') }}
                    </h6>
                    <span class="badge bg-warning text-dark">{{ $stats['low_stock_count'] }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ \App\Helpers\Helpers::translate('product') }}</th>
                                    <th class="text-center">{{ __('admin.dash_current_stock') }}</th>
                                    <th class="text-center">{{ __('admin.dash_min_stock') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockProducts as $prod)
                                <tr>
                                    <td class="ps-3 text-truncate" style="max-width:100px;">
                                        {{ $prod->name }}
                                    </td>
                                    <td class="text-center fw-bold {{ $prod->current_stock <= 0 ? 'text-danger' : 'text-warning' }}">
                                        {{ $prod->current_stock }}
                                    </td>
                                    <td class="text-center text-muted">{{ $prod->min_stock }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ \App\Helpers\Helpers::translate('no_records_found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Pharmacies --}}
        <div class="col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-trophy me-2 text-warning"></i>{{ __('admin.dash_top_pharmacies') }}</h6>
                    <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">
                        {{ \App\Helpers\Helpers::translate('view_all') }}
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ \App\Helpers\Helpers::translate('pharmacy') }}</th>
                                    <th class="text-end">{{ \App\Helpers\Helpers::translate('amount') }}</th>
                                    <th class="text-center">{{ \App\Helpers\Helpers::translate('orders') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topPharmacies as $ph)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('admin.pharmacies.statement', $ph) }}"
                                           class="text-decoration-none text-truncate d-inline-block" style="max-width:100px;">
                                            {{ $ph->name }}
                                        </a>
                                    </td>
                                    <td class="text-end fw-medium text-success">
                                        {{ number_format($ph->payments_sum_amount ?? 0, 0) }}
                                    </td>
                                    <td class="text-center text-muted">{{ $ph->orders_count }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        {{ \App\Helpers\Helpers::translate('no_records_found') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end row 3 --}}
</div>
@endsection

@push('scripts')
<style>
    .bg-soft-primary { background-color: rgba(79,70,229,.1) !important; }
    .text-primary    { color: var(--primary) !important; }
</style>
@endpush
