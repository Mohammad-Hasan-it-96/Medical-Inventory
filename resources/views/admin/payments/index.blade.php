@extends('layouts.app')
@section('title', \App\Helpers\Helpers::translate('payments'))
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-cash-coin me-2"></i>{{ \App\Helpers\Helpers::translate('payments') }}</h3>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.payments.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('pharmacy') }}</label>
                    <select name="pharmacy_id" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        @foreach($pharmacies as $ph)
                            <option value="{{ $ph->id }}" {{ request('pharmacy_id')==$ph->id?'selected':'' }}>{{ $ph->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('date_from') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('date_to') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter me-1"></i>{{ \App\Helpers\Helpers::translate('apply_filters') }}</button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
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
                            <th>{{ \App\Helpers\Helpers::translate('pharmacy') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('order') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('amount') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('method') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('created_by') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('paid_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td class="ps-4">{{ $p->id }}</td>
                            <td>{{ $p->pharmacy?->name ?? '-' }}</td>
                            <td>{{ $p->order?->order_number ?? '-' }}</td>
                            <td class="fw-medium text-success">{{ number_format($p->amount, 2) }}</td>
                            <td><span class="badge bg-info text-dark">{{ ucfirst($p->method ?? '-') }}</span></td>
                            <td>{{ $p->creator?->name ?? '-' }}</td>
                            <td>{{ $p->paid_at?->format('Y-m-d H:i') ?? '-' }}</td>
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
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection