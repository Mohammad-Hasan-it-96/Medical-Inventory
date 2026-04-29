@extends('layouts.app')
@section('title', __('pharmacies.show') . ' – ' . $pharmacy->name)
@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h3 class="mb-0">
                <i class="bi bi-hospital me-2"></i>{{ $pharmacy->name }}
                @if($pharmacy->is_active)
                    <span class="badge bg-success fs-6 ms-2">{{ \App\Helpers\Helpers::translate('active') }}</span>
                @else
                    <span class="badge bg-secondary fs-6 ms-2">{{ \App\Helpers\Helpers::translate('inactive') }}</span>
                @endif
            </h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pharmacies.statement', $pharmacy) }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-journal-text me-1"></i>{{ __('admin.statement_title') }}
            </a>
            <a href="{{ route('admin.pharmacies.edit', $pharmacy) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil-square me-1"></i>{{ __('pharmacies.buttons.edit') }}
            </a>
            <form action="{{ route('admin.pharmacies.destroy', $pharmacy) }}" method="POST"
                  onsubmit="return confirm('{{ __('pharmacies.messages.delete_confirm') }}')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>{{ __('pharmacies.buttons.delete') }}
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ── Contact Information ─────────────────────────────────────── --}}
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-header fw-semibold">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>{{ __('pharmacies.sections.contact') }}
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">{{ __('pharmacies.fields.name') }}</dt>
                        <dd class="col-sm-7">{{ $pharmacy->name }}</dd>

                        <dt class="col-sm-5 text-muted">{{ __('pharmacies.fields.phone') }}</dt>
                        <dd class="col-sm-7">
                            @if($pharmacy->phone)
                                <a href="tel:{{ $pharmacy->phone }}">{{ $pharmacy->phone }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5 text-muted">{{ __('pharmacies.fields.area') }}</dt>
                        <dd class="col-sm-7">{{ $pharmacy->area ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted">{{ __('pharmacies.fields.address') }}</dt>
                        <dd class="col-sm-7">{{ $pharmacy->address ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted">{{ __('pharmacies.fields.rep') }}</dt>
                        <dd class="col-sm-7">{{ $pharmacy->rep?->name ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted">{{ __('pharmacies.fields.created_at') }}</dt>
                        <dd class="col-sm-7">{{ $pharmacy->created_at->format('Y-m-d') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- ── Financial Information ────────────────────────────────────── --}}
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-header fw-semibold">
                    <i class="bi bi-cash-stack me-2 text-success"></i>{{ __('pharmacies.sections.financial') }}
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6 text-muted">{{ __('pharmacies.fields.credit_limit') }}</dt>
                        <dd class="col-sm-6 fw-semibold">{{ number_format($pharmacy->credit_limit, 2) }}</dd>

                        <dt class="col-sm-6 text-muted">{{ __('pharmacies.fields.opening_balance') }}</dt>
                        <dd class="col-sm-6 fw-semibold">{{ number_format($pharmacy->opening_balance, 2) }}</dd>

                        <dt class="col-sm-6 text-muted">{{ __('pharmacies.stats.total_orders') }}</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-info text-dark">{{ $pharmacy->orders_count }}</span>
                        </dd>

                        <dt class="col-sm-6 text-muted">{{ __('pharmacies.stats.total_payments') }}</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-success">{{ number_format($pharmacy->payments_sum_amount ?? 0, 2) }}</span>
                        </dd>
                    </dl>

                    @if($pharmacy->notes)
                    <hr>
                    <p class="text-muted small mb-1 fw-semibold">{{ __('pharmacies.fields.notes') }}</p>
                    <p class="mb-0">{{ $pharmacy->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Recent Orders ────────────────────────────────────────────── --}}
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-cart3 me-2 text-warning"></i>{{ __('pharmacies.sections.orders') }}</span>
                </div>
                <div class="card-body p-0">
                    @if($recentOrders->isEmpty())
                        <p class="text-center text-muted py-4 mb-0">{{ __('pharmacies.stats.no_orders') }}</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">{{ \App\Helpers\Helpers::translate('order_number') }}</th>
                                    <th>{{ \App\Helpers\Helpers::translate('status') }}</th>
                                    <th class="text-end">{{ \App\Helpers\Helpers::translate('total') }}</th>
                                    <th class="text-end pe-4">{{ \App\Helpers\Helpers::translate('date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none fw-medium">
                                            #{{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge
                                            @if($order->status === 'confirmed') bg-success
                                            @elseif($order->status === 'cancelled') bg-danger
                                            @else bg-warning text-dark
                                            @endif">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format($order->total, 2) }}</td>
                                    <td class="text-end pe-4">{{ $order->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Recent Payments ──────────────────────────────────────────── --}}
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header fw-semibold">
                    <i class="bi bi-credit-card me-2 text-success"></i>{{ __('pharmacies.sections.payments') }}
                </div>
                <div class="card-body p-0">
                    @if($recentPayments->isEmpty())
                        <p class="text-center text-muted py-4 mb-0">{{ __('pharmacies.stats.no_payments') }}</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>{{ \App\Helpers\Helpers::translate('method') }}</th>
                                    <th class="text-end">{{ \App\Helpers\Helpers::translate('amount') }}</th>
                                    <th class="text-end pe-4">{{ \App\Helpers\Helpers::translate('date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                <tr>
                                    <td class="ps-4">{{ $payment->id }}</td>
                                    <td>{{ $payment->method ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-end pe-4">{{ $payment->paid_at?->format('Y-m-d') ?? $payment->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- end row --}}
</div>
@endsection

