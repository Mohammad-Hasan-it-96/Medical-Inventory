@extends('layouts.app')
@section('title', __('admin.payment_create'))
@section('content')
<div class="container-fluid">

    {{-- ── Page header ───────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0"><i class="bi bi-cash-coin me-2"></i>{{ __('admin.payment_create') }}</h3>
    </div>

    {{-- ── Validation errors ──────────────────────────────────────────────── --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Form ──────────────────────────────────────────────────────────── --}}
    <div class="card shadow" style="max-width: 700px;">
        <div class="card-body">
            <form action="{{ route('admin.payments.store') }}" method="POST">
                @csrf

                {{-- Pharmacy --}}
                <div class="mb-3">
                    <label for="pharmacy_id" class="form-label fw-semibold">
                        {{ \App\Helpers\Helpers::translate('pharmacy') }} <span class="text-danger">*</span>
                    </label>
                    <select id="pharmacy_id" name="pharmacy_id"
                            class="form-select @error('pharmacy_id') is-invalid @enderror" required>
                        <option value="">{{ __('admin.select_pharmacy') }}</option>
                        @foreach($pharmacies as $ph)
                            <option value="{{ $ph->id }}"
                                {{ old('pharmacy_id') == $ph->id ? 'selected' : '' }}>
                                {{ $ph->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('pharmacy_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Optional order (filtered by chosen pharmacy via JS) --}}
                <div class="mb-3">
                    <label for="order_id" class="form-label fw-semibold">
                        {{ __('admin.payment_order') }}
                    </label>
                    <select id="order_id" name="order_id"
                            class="form-select @error('order_id') is-invalid @enderror">
                        <option value="">{{ __('admin.select_order') }}</option>
                        @foreach($orders as $ord)
                            <option value="{{ $ord->id }}"
                                    data-pharmacy="{{ $ord->pharmacy_id }}"
                                    {{ old('order_id') == $ord->id ? 'selected' : '' }}>
                                {{ $ord->order_number }}
                                ({{ $ord->pharmacy?->name }})
                                — {{ number_format($ord->total, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('order_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text text-muted">
                        {{ \App\Helpers\Helpers::translate('select_pharmacy_first') }}
                    </div>
                </div>

                {{-- Amount --}}
                <div class="mb-3">
                    <label for="amount" class="form-label fw-semibold">
                        {{ __('admin.payment_amount') }} <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" id="amount" name="amount"
                           class="form-control @error('amount') is-invalid @enderror"
                           value="{{ old('amount') }}" required>
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Method --}}
                <div class="mb-3">
                    <label for="method" class="form-label fw-semibold">
                        {{ __('admin.payment_method_lbl') }}
                    </label>
                    <select id="method" name="method"
                            class="form-select @error('method') is-invalid @enderror">
                        <option value="cash"  {{ old('method','cash') === 'cash'  ? 'selected' : '' }}>
                            {{ __('admin.payment_method.cash') }}
                        </option>
                        <option value="bank"  {{ old('method') === 'bank'  ? 'selected' : '' }}>
                            {{ __('admin.payment_method.bank') }}
                        </option>
                        <option value="other" {{ old('method') === 'other' ? 'selected' : '' }}>
                            {{ __('admin.payment_method.other') }}
                        </option>
                    </select>
                    @error('method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Paid At --}}
                <div class="mb-3">
                    <label for="paid_at" class="form-label fw-semibold">
                        {{ __('admin.payment_paid_at') }}
                    </label>
                    <input type="date" id="paid_at" name="paid_at"
                           class="form-control @error('paid_at') is-invalid @enderror"
                           value="{{ old('paid_at', now()->toDateString()) }}">
                    @error('paid_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <label for="notes" class="form-label fw-semibold">
                        {{ __('admin.payment_notes') }}
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="form-control @error('notes') is-invalid @enderror"
                              maxlength="1000">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>{{ \App\Helpers\Helpers::translate('save') }}
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
                        {{ \App\Helpers\Helpers::translate('cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- ── JS: filter orders by selected pharmacy ─────────────────────────── --}}
<script>
(function () {
    const pharmacySel = document.getElementById('pharmacy_id');
    const orderSel    = document.getElementById('order_id');

    // Store all order options (skip placeholder)
    const allOptions = Array.from(orderSel.options).slice(1);

    function filterOrders() {
        const pharmacyId = pharmacySel.value;

        // Remove everything after placeholder
        while (orderSel.options.length > 1) {
            orderSel.remove(1);
        }

        allOptions.forEach(opt => {
            if (!pharmacyId || opt.dataset.pharmacy === pharmacyId) {
                orderSel.add(opt.cloneNode(true));
            }
        });
    }

    pharmacySel.addEventListener('change', function () {
        orderSel.value = '';
        filterOrders();
    });

    // Run on page load to restore old selection
    filterOrders();

    @if(old('order_id'))
    orderSel.value = '{{ old('order_id') }}';
    @endif
})();
</script>
@endsection

