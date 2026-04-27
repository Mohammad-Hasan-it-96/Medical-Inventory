@extends('layouts.app')
@section('title', \App\Helpers\Helpers::translate('edit_company'))

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.companies.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <h3 class="mb-0"><i class="bi bi-buildings me-2"></i>{{ \App\Helpers\Helpers::translate('edit_company') }}: {{ $company->name }}</h3>
    </div>

    <div class="card shadow" style="max-width:720px;">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('admin.companies.update', $company) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $company->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{$message}}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('phone') }}</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $company->phone) }}">
                        @error('phone')<div class="invalid-feedback">{{$message}}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('address') }}</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $company->address) }}">
                        @error('address')<div class="invalid-feedback">{{$message}}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ \App\Helpers\Helpers::translate('notes') }}</label>
                        <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $company->notes) }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{$message}}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">{{ \App\Helpers\Helpers::translate('active') }}</label>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary me-2"><i class="bi bi-save me-1"></i>{{ \App\Helpers\Helpers::translate('update') }}</button>
                        <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>{{ \App\Helpers\Helpers::translate('cancel') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

