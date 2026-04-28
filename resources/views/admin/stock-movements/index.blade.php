@extends('layouts.app')
@section('title', \App\Helpers\Helpers::translate('stock_movements'))
@section('content')
<div class="container-fluid">

    {{-- ── Page header ──────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-arrow-left-right me-2"></i>{{ \App\Helpers\Helpers::translate('stock_movements') }}</h3>
        <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#adjustForm">
            <i class="bi bi-plus-circle me-1"></i>{{ __('admin.stock_adjust_title') }}
        </button>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────────────────── --}}
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

    {{-- ── Manual adjustment form (collapsible) ────────────────────────────── --}}
    <div class="collapse {{ $errors->adjust->any() || old('product_id') ? 'show' : '' }}" id="adjustForm">
        <div class="card shadow mb-4 border-primary">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-sliders me-1"></i>{{ __('admin.stock_adjust_title') }}
            </div>
            <div class="card-body">
                <form action="{{ route('admin.stock-movements.adjust') }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">{{ \App\Helpers\Helpers::translate('product') }} <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select form-select-sm @error('product_id','adjust') is-invalid @enderror" required>
                            <option value="">— {{ \App\Helpers\Helpers::translate('select') }} —</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('product_id','adjust')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">
                            {{ \App\Helpers\Helpers::translate('quantity') }} <span class="text-danger">*</span>
                            <span class="text-muted fw-normal">(+/-)</span>
                        </label>
                        <input type="number" name="quantity" class="form-control form-control-sm @error('quantity','adjust') is-invalid @enderror"
                               value="{{ old('quantity') }}" placeholder="e.g. 10 or -5" required>
                        @error('quantity','adjust')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">{{ \App\Helpers\Helpers::translate('notes') }}</label>
                        <input type="text" name="notes" class="form-control form-control-sm" value="{{ old('notes') }}" maxlength="500">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-check-circle me-1"></i>{{ \App\Helpers\Helpers::translate('save') }}
                        </button>
                        <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Filters ───────────────────────────────────────────────────────────── --}}
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.stock-movements.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('product') }}</label>
                    <select name="product_id" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('type') }}</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        @foreach($types as $t)
                        <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>
                            {{ __('admin.stock_type.' . $t) }}
                        </option>
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
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-filter me-1"></i>{{ \App\Helpers\Helpers::translate('apply_filters') }}
                    </button>
                    <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Table ─────────────────────────────────────────────────────────────── --}}
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>{{ \App\Helpers\Helpers::translate('product') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('type') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('quantity') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('reference') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('notes') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('created_by') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Badge colour per movement type
                            $typeBadge = [
                                'opening'     => 'info',
                                'purchase'    => 'success',
                                'sale'        => 'danger',
                                'sale_cancel' => 'warning',
                                'adjustment'  => 'secondary',
                                'return_in'   => 'success',
                                'return_out'  => 'warning',
                            ];
                        @endphp
                        @forelse($movements as $m)
                        <tr>
                            <td class="ps-4">{{ $m->id }}</td>
                            <td>{{ $m->product?->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $typeBadge[$m->type] ?? 'secondary' }}">
                                    {{ __('admin.stock_type.' . $m->type) }}
                                </span>
                            </td>
                            <td class="fw-medium {{ $m->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}
                            </td>
                            <td class="text-muted small">
                                @if($m->reference_type && $m->reference_id)
                                    {{ class_basename($m->reference_type) }} #{{ $m->reference_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-muted small">{{ $m->notes ?? '—' }}</td>
                            <td>{{ $m->creator?->name ?? '-' }}</td>
                            <td>{{ $m->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-left-right fs-1 d-block mb-2"></i>
                                {{ \App\Helpers\Helpers::translate('no_records_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($movements->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $movements->links() }}</div>
        @endif
    </div>
</div>
@endsection
