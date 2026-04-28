@extends('layouts.app')
@section('title', __('admin.order_title', ['number' => $order->order_number]))
@section('content')
<div class="container-fluid">

    {{-- ── Page header ────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0"><i class="bi bi-receipt me-2"></i>{{ __('admin.order_title', ['number' => $order->order_number]) }}</h3>
        @php $sc = ['draft'=>'secondary','pending'=>'warning','confirmed'=>'success','cancelled'=>'danger']; @endphp
        <span class="badge bg-{{ $sc[$order->status] ?? 'secondary' }} ms-2">
            {{ __('admin.order_status.' . $order->status) }}
        </span>

        {{-- ── Action buttons (only for allowed statuses) ── --}}
        @if(in_array($order->status, [\App\Models\Order::STATUS_DRAFT, \App\Models\Order::STATUS_PENDING]))
        <form action="{{ route('admin.orders.confirm', $order) }}" method="POST" class="ms-auto"
              onsubmit="return confirm('{{ __('admin.confirm_order_confirm') }}')">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">
                <i class="bi bi-check-circle me-1"></i>{{ __('admin.confirm_order') }}
            </button>
        </form>
        @endif

        @if($order->status !== \App\Models\Order::STATUS_CANCELLED)
        <form action="{{ route('admin.orders.cancel', $order) }}" method="POST"
              @if($order->status !== \App\Models\Order::STATUS_DRAFT && $order->status !== \App\Models\Order::STATUS_PENDING) class="ms-auto" @endif
              onsubmit="return confirm('{{ __('admin.cancel_order_confirm') }}')">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-x-circle me-1"></i>{{ __('admin.cancel_order') }}
            </button>
        </form>
        @endif
    </div>

    {{-- ── Flash messages ────────────────────────────────────────────────── --}}
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

    {{-- ── Body row ─────────────────────────────────────────────────────── --}}
    <div class="row g-4">

        {{-- Order details card --}}
        <div class="col-md-5">
            <div class="card shadow h-100">
                <div class="card-header fw-semibold">{{ __('admin.order_details') }}</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">{{ \App\Helpers\Helpers::translate('pharmacy') }}</dt>
                        <dd class="col-7">{{ $order->pharmacy?->name ?? '-' }}</dd>

                        <dt class="col-5">{{ \App\Helpers\Helpers::translate('rep') }}</dt>
                        <dd class="col-7">{{ $order->rep?->name ?? '-' }}</dd>

                        <dt class="col-5">{{ \App\Helpers\Helpers::translate('subtotal') }}</dt>
                        <dd class="col-7">{{ number_format($order->subtotal, 2) }}</dd>

                        <dt class="col-5">{{ \App\Helpers\Helpers::translate('discount') }}</dt>
                        <dd class="col-7">{{ number_format($order->discount, 2) }}</dd>

                        <dt class="col-5">{{ \App\Helpers\Helpers::translate('total') }}</dt>
                        <dd class="col-7 fw-bold text-success">{{ number_format($order->total, 2) }}</dd>

                        <dt class="col-5">{{ \App\Helpers\Helpers::translate('created_at') }}</dt>
                        <dd class="col-7">{{ $order->created_at?->format('Y-m-d H:i') }}</dd>

                        @if($order->confirmed_at)
                        <dt class="col-5">{{ __('admin.confirmed_at') }}</dt>
                        <dd class="col-7">{{ $order->confirmed_at->format('Y-m-d H:i') }}</dd>
                        @endif

                        @if($order->cancelled_at)
                        <dt class="col-5">{{ __('admin.cancelled_at') }}</dt>
                        <dd class="col-7">{{ $order->cancelled_at->format('Y-m-d H:i') }}</dd>
                        @endif

                        @if($order->notes)
                        <dt class="col-5">{{ \App\Helpers\Helpers::translate('notes') }}</dt>
                        <dd class="col-7">{{ $order->notes }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        {{-- Items table --}}
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header fw-semibold">{{ __('admin.order_items') }}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ \App\Helpers\Helpers::translate('product') }}</th>
                                    <th class="text-end">{{ \App\Helpers\Helpers::translate('qty') }}</th>
                                    <th class="text-end">{{ \App\Helpers\Helpers::translate('unit_price') }}</th>
                                    <th class="text-end">{{ \App\Helpers\Helpers::translate('discount') }}</th>
                                    <th class="text-end pe-3">{{ \App\Helpers\Helpers::translate('total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->orderItems as $item)
                                <tr>
                                    <td class="ps-3">{{ $item->product?->name ?? '-' }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                                    <td class="text-end pe-3 fw-medium">{{ number_format($item->total, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">
                                        {{ __('admin.no_items') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($order->orderItems->isNotEmpty())
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end pe-2 fw-semibold">{{ \App\Helpers\Helpers::translate('total') }}</td>
                                    <td class="text-end pe-3 fw-bold text-success">{{ number_format($order->total, 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
