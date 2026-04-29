@extends('layouts.app')
@section('title', __('admin.statement_for', ['name' => $pharmacy->name]))
@section('content')
<div class="container-fluid">

    {{-- ── Page header ─────────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.pharmacies.show', $pharmacy) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h3 class="mb-0">
                <i class="bi bi-journal-text me-2"></i>
                {{ __('admin.statement_for', ['name' => $pharmacy->name]) }}
            </h3>
        </div>
        <a href="{{ route('admin.pharmacies.show', $pharmacy) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-hospital me-1"></i>{{ $pharmacy->name }}
        </a>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────────────────── --}}
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── Summary cards ────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1 small">{{ __('admin.statement_opening') }}</h6>
                    <h4 class="fw-bold mb-0">{{ number_format($opening_balance, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1 small">{{ __('admin.statement_debit') }}</h6>
                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($total_debit, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1 small">{{ __('admin.statement_credit') }}</h6>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($total_credit, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 {{ $balance > 0 ? 'border-danger border' : '' }}">
                <div class="card-body">
                    <h6 class="text-muted mb-1 small">{{ __('admin.statement_balance') }}</h6>
                    <h4 class="fw-bold mb-0 {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($balance, 2) }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Date filter ──────────────────────────────────────────────────────── --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.pharmacies.statement', $pharmacy) }}" method="GET"
                  class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('date_from') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('date_to') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-filter me-1"></i>{{ \App\Helpers\Helpers::translate('apply_filters') }}
                    </button>
                    <a href="{{ route('admin.pharmacies.statement', $pharmacy) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
                <div class="col-md-2 text-end">
                    <small class="text-muted">
                        {{ __('admin.statement_no_entries') !== __('admin.statement_no_entries')
                            ? '' : '' }}
                        @if(request('date_from') || request('date_to'))
                            <span class="badge bg-info">
                                {{ request('date_from', '…') }} → {{ request('date_to', '…') }}
                            </span>
                        @else
                            <span class="badge bg-secondary">{{ \App\Helpers\Helpers::translate('all') }}</span>
                        @endif
                    </small>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Ledger table ─────────────────────────────────────────────────────── --}}
    <div class="card shadow">
        <div class="card-header fw-semibold">
            <i class="bi bi-list-columns-reverse me-2"></i>{{ __('pharmacies.sections.statement') }}
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-4">{{ __('admin.statement_entry_date') }}</th>
                            <th>{{ __('admin.statement_entry_type') }}</th>
                            <th>{{ __('admin.statement_entry_desc') }}</th>
                            <th>{{ __('admin.statement_entry_order') }}</th>
                            <th class="text-end pe-4">{{ __('admin.statement_entry_amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                        <tr>
                            <td class="ps-4">{{ $entry->entry_date }}</td>
                            <td>
                                @if($entry->type === 'debit')
                                    <span class="badge bg-danger">{{ __('admin.entry_type_debit') }}</span>
                                @else
                                    <span class="badge bg-success">{{ __('admin.entry_type_credit') }}</span>
                                @endif
                            </td>
                            <td>{{ $entry->description ?? '—' }}</td>
                            <td>
                                @if($entry->order)
                                    <a href="{{ route('admin.orders.show', $entry->order) }}"
                                       class="text-decoration-none small">
                                        {{ $entry->order->order_number }}
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-4 fw-medium {{ $entry->type === 'debit' ? 'text-danger' : 'text-success' }}">
                                {{ number_format($entry->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                {{ __('admin.statement_no_entries') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($entries->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $entries->links() }}</div>
        @endif
    </div>

</div>
@endsection

