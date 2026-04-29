@extends('layouts.app')
@section('title', __('pharmacies.title'))
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
        <h3><i class="bi bi-hospital me-2"></i>{{ __('pharmacies.title') }}</h3>
        <a href="{{ route('admin.pharmacies.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>{{ __('pharmacies.buttons.add') }}
        </a>
    </div>

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

    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.pharmacies.index') }}" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="col-md-3">
                    <label class="form-label small">{{ __('pharmacies.filters.search') }}</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}"
                           placeholder="{{ __('pharmacies.filters.search_placeholder') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ __('pharmacies.fields.rep') }}</label>
                    <select name="rep_id" class="form-select form-select-sm">
                        <option value="">{{ __('pharmacies.filters.all_reps') }}</option>
                        @foreach($reps as $rep)
                            <option value="{{ $rep->id }}" {{ request('rep_id') == $rep->id ? 'selected' : '' }}>
                                {{ $rep->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ __('pharmacies.fields.status') }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ __('pharmacies.filters.all') }}</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ \App\Helpers\Helpers::translate('active') }}</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ \App\Helpers\Helpers::translate('inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-filter me-1"></i>{{ __('pharmacies.filters.apply') }}
                    </button>
                    <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>{{ __('pharmacies.filters.clear') }}
                    </a>
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
                                    {{ __('pharmacies.fields.name') }} <i class="bi {{ $sortIcon('name') }} small"></i>
                                </a>
                            </th>
                            <th>{{ __('pharmacies.fields.phone') }}</th>
                            <th>
                                <a href="{{ $sortUrl('area') }}" class="text-dark text-decoration-none d-flex align-items-center gap-1">
                                    {{ __('pharmacies.fields.area') }} <i class="bi {{ $sortIcon('area') }} small"></i>
                                </a>
                            </th>
                            <th>{{ __('pharmacies.fields.rep') }}</th>
                            <th class="text-end">
                                <a href="{{ $sortUrl('credit_limit') }}" class="text-dark text-decoration-none d-flex align-items-center justify-content-end gap-1">
                                    {{ __('pharmacies.fields.credit_limit') }} <i class="bi {{ $sortIcon('credit_limit') }} small"></i>
                                </a>
                            </th>
                            <th>{{ __('pharmacies.fields.status') }}</th>
                            <th class="text-end pe-4">{{ \App\Helpers\Helpers::translate('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pharmacies as $pharmacy)
                        <tr>
                            <td class="ps-4">{{ $pharmacy->id }}</td>
                            <td class="fw-medium">
                                <a href="{{ route('admin.pharmacies.show', $pharmacy) }}" class="text-decoration-none">
                                    {{ $pharmacy->name }}
                                </a>
                            </td>
                            <td>{{ $pharmacy->phone ?? '—' }}</td>
                            <td>{{ $pharmacy->area ?? '—' }}</td>
                            <td>{{ $pharmacy->rep?->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($pharmacy->credit_limit, 2) }}</td>
                            <td>
                                @if($pharmacy->is_active)
                                    <span class="badge bg-success">{{ \App\Helpers\Helpers::translate('active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ \App\Helpers\Helpers::translate('inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('admin.pharmacies.show', $pharmacy) }}"
                                       class="btn btn-sm btn-light rounded-circle p-2" title="{{ __('pharmacies.buttons.view') }}">
                                        <i class="bi bi-eye text-info"></i>
                                    </a>
                                    <a href="{{ route('admin.pharmacies.statement', $pharmacy) }}"
                                       class="btn btn-sm btn-light rounded-circle p-2" title="{{ __('admin.statement_title') }}">
                                        <i class="bi bi-journal-text text-secondary"></i>
                                    </a>
                                    <a href="{{ route('admin.pharmacies.edit', $pharmacy) }}"
                                       class="btn btn-sm btn-light rounded-circle p-2" title="{{ __('pharmacies.buttons.edit') }}">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <form action="{{ route('admin.pharmacies.destroy', $pharmacy) }}" method="POST"
                                          onsubmit="return confirm('{{ __('pharmacies.messages.delete_confirm') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light rounded-circle p-2" title="{{ __('pharmacies.buttons.delete') }}">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-hospital fs-1 d-block mb-2"></i>
                                {{ __('pharmacies.no_records') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.pharmacies.index') }}" class="d-flex align-items-center gap-2">
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
            @if($pharmacies->hasPages())
                <div>{{ $pharmacies->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
