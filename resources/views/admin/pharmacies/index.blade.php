@extends('layouts.app')
@section('title', __('pharmacies.title'))
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-hospital me-2"></i>{{ __('pharmacies.title') }}</h3>
        <a href="{{ route('admin.pharmacies.create') }}" class="btn btn-primary">
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

    {{-- Filters --}}
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.pharmacies.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">{{ __('pharmacies.filters.search') }}</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ request('search') }}"
                           placeholder="{{ __('pharmacies.filters.search_placeholder') }}">
                </div>
                <div class="col-md-3">
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
                <div class="col-md-4 d-flex gap-2">
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

    {{-- Table --}}
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>{{ __('pharmacies.fields.name') }}</th>
                            <th>{{ __('pharmacies.fields.phone') }}</th>
                            <th>{{ __('pharmacies.fields.area') }}</th>
                            <th>{{ __('pharmacies.fields.rep') }}</th>
                            <th class="text-end">{{ __('pharmacies.fields.credit_limit') }}</th>
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
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('admin.pharmacies.show', $pharmacy) }}"
                                       class="btn btn-sm btn-light rounded-circle p-2"
                                       title="{{ __('pharmacies.buttons.view') }}">
                                        <i class="bi bi-eye text-info"></i>
                                    </a>
                                    <a href="{{ route('admin.pharmacies.edit', $pharmacy) }}"
                                       class="btn btn-sm btn-light rounded-circle p-2"
                                       title="{{ __('pharmacies.buttons.edit') }}">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <form action="{{ route('admin.pharmacies.destroy', $pharmacy) }}" method="POST"
                                          onsubmit="return confirm('{{ __('pharmacies.messages.delete_confirm') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light rounded-circle p-2"
                                                title="{{ __('pharmacies.buttons.delete') }}">
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
        @if($pharmacies->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $pharmacies->links() }}</div>
        @endif
    </div>
</div>
@endsection
