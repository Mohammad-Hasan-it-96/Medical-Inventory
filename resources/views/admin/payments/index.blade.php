@extends('layouts.app')
@section('title', __('admin.payment_title'))
@section('content')
@php
$sortIcon = fn($col) => $orderBy === $col
    ? ($direction === 'asc' ? 'bi-sort-up-alt' : 'bi-sort-down-alt')
    : 'bi-arrow-down-up opacity-25';
$sortUrl = fn($col) => request()->fullUrlWithQuery([
    'order_by'  => $col,
    'direction' => ($orderBy === $col && $direction === 'asc') ? 'desc' : 'asc',
    'page'      => 1,
]);
@endphp
<div class="container-fluid">

    {{-- ── Page header ───────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-cash-coin me-2"></i>{{ __('admin.payment_title') }}</h3>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>{{ __('admin.payment_create') }}
        </a>
    </div>

    {{-- ── Flash alerts ───────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Filters ────────────────────────────────────────────────────────── --}}
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.payments.index') }}" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="col-md-3">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('pharmacy') }}</label>
                    <select name="pharmacy_id" class="form-select form-select-sm">
                        <option value="">{{ __('admin.payment_method.all') }}</option>
                        @foreach($pharmacies as $ph)
                            <option value="{{ $ph->id }}" {{ request('pharmacy_id') == $ph->id ? 'selected' : '' }}>
                                {{ $ph->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ __('admin.payment_method_lbl') }}</label>
                    <select name="method" class="form-select form-select-sm">
                        <option value="">{{ __('admin.payment_method.all') }}</option>
                        <option value="cash"  {{ request('method') === 'cash'  ? 'selected' : '' }}>{{ __('admin.payment_method.cash') }}</option>
                        <option value="bank"  {{ request('method') === 'bank'  ? 'selected' : '' }}>{{ __('admin.payment_method.bank') }}</option>
                        <option value="other" {{ request('method') === 'other' ? 'selected' : '' }}>{{ __('admin.payment_method.other') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('date_from') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('date_to') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-filter me-1"></i>{{ \App\Helpers\Helpers::translate('apply_filters') }}
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Table ──────────────────────────────────────────────────────────── --}}
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>{{ \App\Helpers\Helpers::translate('pharmacy') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('order_number') }}</th>
                            <th>
                                <a href="{{ $sortUrl('amount') }}" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                    {{ __('admin.payment_amount') }} <i class="bi {{ $sortIcon('amount') }} small"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('method') }}" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                    {{ __('admin.payment_method_lbl') }} <i class="bi {{ $sortIcon('method') }} small"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('paid_at') }}" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                    {{ __('admin.payment_paid_at') }} <i class="bi {{ $sortIcon('paid_at') }} small"></i>
                                </a>
                            </th>
                            <th>{{ \App\Helpers\Helpers::translate('created_by') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td class="ps-4">{{ $p->id }}</td>
                            <td>{{ $p->pharmacy?->name ?? '-' }}</td>
                            <td>
                                @if($p->order)
                                    <a href="{{ route('admin.orders.show', $p->order) }}" class="text-decoration-none">
                                        {{ $p->order->order_number }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="fw-medium text-success">{{ number_format($p->amount, 2) }}</td>
                            <td>
                                @php $mc = ['cash'=>'success','bank'=>'info','other'=>'secondary']; @endphp
                                <span class="badge bg-{{ $mc[$p->method] ?? 'secondary' }}">
                                    {{ __('admin.payment_method.' . ($p->method ?? 'other')) }}
                                </span>
                            </td>
                            <td>{{ $p->paid_at?->format('Y-m-d') }}</td>
                            <td>{{ $p->creator?->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-cash-coin fs-1 d-block mb-2"></i>
                                {{ \App\Helpers\Helpers::translate('no_records_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($payments->count())
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="ps-4 text-end fw-semibold">
                                {{ \App\Helpers\Helpers::translate('total') }}
                            </td>
                            <td class="fw-bold text-success" colspan="4">
                                {{ number_format($payments->sum('amount'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="d-flex align-items-center gap-2">
                @foreach(request()->except(['page','per_page','order_by','direction']) as $k => $v)
                    @if($v !== '' && $v !== null)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                @endforeach
                <input type="hidden" name="order_by" value="{{ $orderBy }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <label class="text-muted small mb-0">{{ __('admin.per_page') }}</label>
                <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    @foreach([10,20,50,100] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            <div>{{ $payments->links() }}</div>
        </div>
        @endif
    </div>

</div>
@endsection

