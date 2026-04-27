@extends('layouts.app')
@section('title', __('admin.order_title', ['number' => $order->order_number]))
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <h3 class="mb-0"><i class="bi bi-receipt me-2"></i>{{ __('admin.order_title', ['number' => $order->order_number]) }}</h3>
        @php $sc = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger']; @endphp
        <span class="badge bg-{{ $sc[$order->status] ?? 'secondary' }} ms-2">{{ __('admin.order_status.' . $order->status) }}</span>
    </div>
    <div class="row g-4">
        <div class="col-md-5">
            <div class="card shadow h-100">
                <div class="card-header"><strong>{{ \App\Helpers\Helpers::translate('order_details') }}</strong></div>
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
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header"><strong>{{ \App\Helpers\Helpers::translate('items') }}</strong></div>
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
                                <tr><td colspan="5" class="text-center py-3 text-muted">{{ __('admin.no_items') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
