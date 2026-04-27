@extends('layouts.app')
@section('title', \App\Helpers\Helpers::translate('edit_pharmacy'))
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <h3 class="mb-0"><i class="bi bi-hospital me-2"></i>{{ \App\Helpers\Helpers::translate('edit_pharmacy') }}: {{ $pharmacy->name }}</h3>
    </div>
    <div class="card shadow" style="max-width:800px;">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('admin.pharmacies.update', $pharmacy) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $pharmacy->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{$message}}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('phone') }}</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $pharmacy->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('area') }}</label>
                        <input type="text" name="area" class="form-control" value="{{ old('area', $pharmacy->area) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('rep') }}</label>
                        <select name="rep_id" class="form-select">
                            <option value="">{{ \App\Helpers\Helpers::translate('none') }}</option>
                            @foreach($reps as $rep)
                                <option value="{{ $rep->id }}" {{ old('rep_id', $pharmacy->rep_id)==$rep->id?'selected':'' }}>{{ $rep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('address') }}</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $pharmacy->address) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('credit_limit') }}</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', $pharmacy->credit_limit) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('opening_balance') }}</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', $pharmacy->opening_balance) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('notes') }}</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $pharmacy->notes) }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $pharmacy->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">{{ \App\Helpers\Helpers::translate('active') }}</label>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary me-2"><i class="bi bi-save me-1"></i>{{ \App\Helpers\Helpers::translate('update') }}</button>
                        <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>{{ \App\Helpers\Helpers::translate('cancel') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection