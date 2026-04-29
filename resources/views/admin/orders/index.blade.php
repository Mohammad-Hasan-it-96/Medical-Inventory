@extends('layouts.app')
@section('title', \App\Helpers\Helpers::translate('orders'))
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-receipt me-2"></i>{{ \App\Helpers\Helpers::translate('orders') }}</h3>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('search') }}</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="{{ __('admin.order_number_search') }}"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('status') }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        <option value="draft"     {{ request('status')==='draft'?'selected':'' }}>{{ __('admin.order_status.draft') }}</option>
                        <option value="pending"   {{ request('status')==='pending'?'selected':'' }}>{{ __('admin.order_status.pending') }}</option>
                        <option value="confirmed" {{ request('status')==='confirmed'?'selected':'' }}>{{ __('admin.order_status.confirmed') }}</option>
                        <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>{{ __('admin.order_status.cancelled') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('rep') }}</label>
                    <select name="rep_id" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        @foreach($reps as $rep)
                            <option value="{{ $rep->id }}" {{ request('rep_id')==$rep->id?'selected':'' }}>{{ $rep->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('pharmacy') }}</label>
                    <select name="pharmacy_id" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        @foreach($pharmacies as $ph)
                            <option value="{{ $ph->id }}" {{ request('pharmacy_id')==$ph->id?'selected':'' }}>{{ $ph->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('date_from') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('date_to') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter me-1"></i>{{ \App\Helpers\Helpers::translate('apply_filters') }}</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
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
                            <th>{{ \App\Helpers\Helpers::translate('order_number') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('pharmacy') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('rep') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('total') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('status') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('date') }}</th>
                            <th class="text-end pe-4">{{ \App\Helpers\Helpers::translate('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="ps-4">{{ $order->id }}</td>
                            <td class="fw-medium">{{ $order->order_number }}</td>
                            <td>{{ $order->pharmacy?->name ?? '-' }}</td>
                            <td>{{ $order->rep?->name ?? '-' }}</td>
                            <td>{{ number_format($order->total, 2) }}</td>
                            <td>
                                @php $sc = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger']; @endphp
                                <span class="badge bg-{{ $sc[$order->status] ?? 'secondary' }}">
                                    {{ __('admin.order_status.' . $order->status) }}
                                </span>
                            </td>
                            <td>{{ $order->created_at?->format('Y-m-d') }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-light rounded-circle p-2">
                                    <i class="bi bi-eye text-primary"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                {{ \App\Helpers\Helpers::translate('no_records_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
