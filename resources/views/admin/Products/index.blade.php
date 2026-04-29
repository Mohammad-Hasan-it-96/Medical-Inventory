@extends('layouts.app')
@section('title', \App\Helpers\Helpers::translate('manage_products'))
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

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between mb-4">
        <h3><i class="bi bi-capsule me-2"></i>{{\App\Helpers\Helpers::translate('product_management')}}</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>{{\App\Helpers\Helpers::translate('add_new_product')}}
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-file-excel me-1"></i>{{\App\Helpers\Helpers::translate('excel')}}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">{{\App\Helpers\Helpers::translate('export')}}</li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.products.export') }}">
                            <i class="bi bi-file-earmark-excel me-2"></i>
                            @if(Auth::user()->role === 'admin')
                                {{\App\Helpers\Helpers::translate('export_all_products')}}
                            @else
                                {{\App\Helpers\Helpers::translate('export_your_products')}}
                            @endif
                        </a>
                    </li>
                    @if(Auth::user()->role === 'admin')
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header">{{\App\Helpers\Helpers::translate('export_by_user')}}</li>
                        @foreach($users as $user)
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.products.export', ['user_id' => $user->id]) }}">
                                    <i class="bi bi-person me-2"></i>{{ $user->name }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li class="dropdown-header">{{\App\Helpers\Helpers::translate('import')}}</li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.products.import') }}">
                            <i class="bi bi-file-earmark-arrow-up me-2"></i>{{\App\Helpers\Helpers::translate('import_products')}}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ── Flash ────────────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="col-md-3">
                    <label class="form-label small">{{\App\Helpers\Helpers::translate('search')}}</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}" placeholder="{{ __('admin.placeholder_name_phone') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{\App\Helpers\Helpers::translate('company')}}</label>
                    <select name="company_id" class="form-select form-select-sm">
                        <option value="">{{\App\Helpers\Helpers::translate('all_companies')}}</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ __('admin.filter_is_active') }}</label>
                    <select name="is_active" class="form-select form-select-sm">
                        <option value="">{{ __('admin.any_status') }}</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ \App\Helpers\Helpers::translate('active') }}</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ \App\Helpers\Helpers::translate('inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{\App\Helpers\Helpers::translate('pharmaceutical_form')}}</label>
                    <select name="form" class="form-select form-select-sm">
                        <option value="">{{ __('admin.any_form') }}</option>
                        @foreach($forms as $f)
                            <option value="{{ $f }}" {{ request('form') === $f ? 'selected' : '' }}>
                                {{ \App\Helpers\Helpers::translate('pharm_form_' . $f) !== 'pharm_form_' . $f
                                    ? \App\Helpers\Helpers::translate('pharm_form_' . $f) : ucfirst($f) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end pb-1">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="low_stock" value="1"
                               id="low_stock" {{ request('low_stock') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="low_stock">
                            {{ __('admin.low_stock_filter') }}
                        </label>
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-filter me-1"></i>{{\App\Helpers\Helpers::translate('apply_filters')}}
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>
                                <a href="{{ $sortUrl('name') }}" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                    {{\App\Helpers\Helpers::translate('product')}} <i class="bi {{ $sortIcon('name') }} small"></i>
                                </a>
                            </th>
                            <th>{{\App\Helpers\Helpers::translate('company')}}</th>
                            <th>
                                <a href="{{ $sortUrl('form') }}" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                    {{\App\Helpers\Helpers::translate('pharmaceutical_form')}} <i class="bi {{ $sortIcon('form') }} small"></i>
                                </a>
                            </th>
                            <th class="text-end">{{\App\Helpers\Helpers::translate('net_price_syp')}}</th>
                            <th class="text-end">{{\App\Helpers\Helpers::translate('public_price_syp')}}</th>
                            <th>{{\App\Helpers\Helpers::translate('stock')}}</th>
                            <th>
                                <a href="{{ $sortUrl('updated_at') }}" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                    {{\App\Helpers\Helpers::translate('updated_at')}} <i class="bi {{ $sortIcon('updated_at') }} small"></i>
                                </a>
                            </th>
                            <th class="text-end pe-4">{{\App\Helpers\Helpers::translate('actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="ps-4 fw-medium text-muted small">{{ $product->id }}</td>
                            <td>
                                <div class="fw-medium">{{ $product->name }}</div>
                                <small class="text-muted">{{ Str::limit($product->details, 40) }}</small>
                            </td>
                            <td>{{ $product->company?->name ?? '—' }}</td>
                            <td>
                                @if($product->form)
                                    <span class="badge bg-info text-dark">
                                        {{ \App\Helpers\Helpers::translate('pharm_form_' . $product->form) !== 'pharm_form_' . $product->form
                                            ? \App\Helpers\Helpers::translate('pharm_form_' . $product->form)
                                            : ucfirst($product->form) }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($product->productPrice?->net_price_syp ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($product->productPrice?->public_price_syp ?? 0, 2) }}</td>
                            <td>
                                @php $liveStock = (int) ($stockMap[$product->id] ?? 0); @endphp
                                @php $minStock = (int) ($product->min_stock ?? 0); @endphp
                                <span class="badge bg-{{ $liveStock <= 0 ? 'danger' : ($minStock > 0 && $liveStock <= $minStock ? 'warning' : 'success') }}">
                                    {{ $liveStock }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $product->updated_at?->format('Y-m-d') }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('admin.products.edit', $product->id) }}"
                                       class="btn btn-sm btn-light rounded-circle p-2">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <form action="{{ route('admin.products.delete', $product->id) }}" method="POST"
                                          onsubmit="return confirm('{{ __('admin.delete_confirm_product') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light rounded-circle p-2">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-database-exclamation fs-1 d-block mb-2"></i>
                                {{\App\Helpers\Helpers::translate('no_products_found')}}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- ── Footer: per-page + pagination ──────────────────────────────── --}}
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.products.index') }}" class="d-flex align-items-center gap-2">
                @foreach(request()->except(['page','per_page','order_by','direction']) as $k => $v)
                    @if($v !== '' && $v !== null)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                @endforeach
                <input type="hidden" name="order_by"  value="{{ $orderBy }}">
                <input type="hidden" name="direction"  value="{{ $direction }}">
                <label class="text-muted small mb-0">{{ __('admin.per_page') }}</label>
                <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    @foreach([10,20,50,100] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            @if($products->hasPages())
                <div>{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

