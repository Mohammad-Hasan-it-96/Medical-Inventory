@extends('layouts.app')
@section('title', \App\Helpers\Helpers::translate('companies'))
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-buildings me-2"></i>{{ \App\Helpers\Helpers::translate('companies') }}</h3>
        <a href="{{ route('admin.companies.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>{{ \App\Helpers\Helpers::translate('add_new') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.companies.index') }}" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="col-md-5">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('search') }}</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('admin.placeholder_name_phone') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('status') }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        <option value="1" {{ request('status')==='1'?'selected':'' }}>{{ \App\Helpers\Helpers::translate('active') }}</option>
                        <option value="0" {{ request('status')==='0'?'selected':'' }}>{{ \App\Helpers\Helpers::translate('inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter me-1"></i>{{ \App\Helpers\Helpers::translate('apply_filters') }}</button>
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>{{ \App\Helpers\Helpers::translate('clear') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>
                                <a href="{{ $sortUrl('name') }}" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                    {{ \App\Helpers\Helpers::translate('name') }} <i class="bi {{ $sortIcon('name') }} small"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('phone') }}" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                    {{ \App\Helpers\Helpers::translate('phone') }} <i class="bi {{ $sortIcon('phone') }} small"></i>
                                </a>
                            </th>
                            <th>{{ \App\Helpers\Helpers::translate('address') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('status') }}</th>
                            <th class="text-end pe-4">{{ \App\Helpers\Helpers::translate('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                        <tr>
                            <td class="ps-4">{{ $company->id }}</td>
                            <td class="fw-medium">{{ $company->name }}</td>
                            <td>{{ $company->phone ?? '—' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($company->address, 40) ?? '—' }}</td>
                            <td>
                                @if($company->is_active)
                                    <span class="badge bg-success">{{ \App\Helpers\Helpers::translate('active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ \App\Helpers\Helpers::translate('inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-light rounded-circle p-2">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <form action="{{ route('admin.companies.destroy', $company) }}" method="POST"
                                          onsubmit="return confirm('{{ \App\Helpers\Helpers::translate('confirm_delete') }}')">
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
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-buildings fs-1 d-block mb-2"></i>
                                {{ \App\Helpers\Helpers::translate('no_records_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.companies.index') }}" class="d-flex align-items-center gap-2">
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
            @if($companies->hasPages())
                <div>{{ $companies->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

