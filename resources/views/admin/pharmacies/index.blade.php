@extends('layouts.app')
@section('title', \App\Helpers\Helpers::translate('pharmacies'))
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-hospital me-2"></i>{{ \App\Helpers\Helpers::translate('pharmacies') }}</h3>
        <a href="{{ route('admin.pharmacies.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>{{ \App\Helpers\Helpers::translate('add_new') }}
        </a>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.pharmacies.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('search') }}</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Name / phone / area">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('rep') }}</label>
                    <select name="rep_id" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        @foreach($reps as $rep)
                            <option value="{{ $rep->id }}" {{ request('rep_id')==$rep->id?'selected':'' }}>{{ $rep->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ \App\Helpers\Helpers::translate('status') }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ \App\Helpers\Helpers::translate('all') }}</option>
                        <option value="1" {{ request('status')==='1'?'selected':'' }}>{{ \App\Helpers\Helpers::translate('active') }}</option>
                        <option value="0" {{ request('status')==='0'?'selected':'' }}>{{ \App\Helpers\Helpers::translate('inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter me-1"></i>{{ \App\Helpers\Helpers::translate('apply_filters') }}</button>
                    <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>{{ \App\Helpers\Helpers::translate('clear') }}</a>
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
                            <th>{{ \App\Helpers\Helpers::translate('name') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('phone') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('area') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('rep') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('credit_limit') }}</th>
                            <th>{{ \App\Helpers\Helpers::translate('status') }}</th>
                            <th class="text-end pe-4">{{ \App\Helpers\Helpers::translate('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pharmacies as $pharmacy)
                        <tr>
                            <td class="ps-4">{{ $pharmacy->id }}</td>
                            <td class="fw-medium">{{ $pharmacy->name }}</td>
                            <td>{{ $pharmacy->phone ?? '-' }}</td>
                            <td>{{ $pharmacy->area ?? '-' }}</td>
                            <td>{{ $pharmacy->rep?->name ?? '-' }}</td>
                            <td>{{ number_format($pharmacy->credit_limit, 2) }}</td>
                            <td>
                                @if($pharmacy->is_active)
                                    <span class="badge bg-success">{{ \App\Helpers\Helpers::translate('active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ \App\Helpers\Helpers::translate('inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('admin.pharmacies.edit', $pharmacy) }}" class="btn btn-sm btn-light rounded-circle p-2">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <form action="{{ route('admin.pharmacies.destroy', $pharmacy) }}" method="POST"
                                          onsubmit="return confirm('Delete this pharmacy?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light rounded-circle p-2"><i class="bi bi-trash text-danger"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-hospital fs-1 d-block mb-2"></i>
                                {{ \App\Helpers\Helpers::translate('no_records_found') }}
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