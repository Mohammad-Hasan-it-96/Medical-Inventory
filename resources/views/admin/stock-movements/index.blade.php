@extends('layouts.app')
@section('title', \App\Helpers\Helpers::translate('stock_movements'))
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-arrow-left-right me-2"></i>{{ \App\Helpers\Helpers::translate('stock_movements') }}</h3>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.stock-movements.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('product') }}</label>
                    <select name="product_id" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('type') }}</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ request('type')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
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
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter me-1"></i>{{ \App\Helpers\Helpers::translate('apply_filters') }}</button>
                    <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
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
                            <th>{{ \App\Helpers\Helpers::translate('product') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('type') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('quantity') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('reference') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('created_by') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $m)
                        <tr>
                            <td class="ps-4">{{ $m->id }}</td>
                            <td>{{ $m->product?->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $m->type === 'in' ? 'success' : ($m->type === 'out' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($m->type) }}
                                </span>
                            </td>
                            <td class="fw-medium {{ $m->type === 'in' ? 'text-success' : 'text-danger' }}">
                                {{ $m->type === 'in' ? '+' : '-' }}{{ $m->quantity }}
                            </td>
                            <td>{{ $m->reference_type ? $m->reference_type . ' #' . $m->reference_id : '-' }}</td>
                            <td>{{ $m->creator?->name ?? '-' }}</td>
                            <td>{{ $m->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
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