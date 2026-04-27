@extends('layouts.app')
@section('title', __('pharmacies.add'))
@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0"><i class="bi bi-hospital me-2"></i>{{ __('pharmacies.add') }}</h3>
    </div>

    <div class="card shadow" style="max-width:800px;">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.pharmacies.store') }}">
                @csrf
                <div class="row g-3">

                    {{-- Name --}}
                    <div class="col-md-6">
                        <label class="form-label">
                            {{ __('pharmacies.fields.name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Phone --}}
                    <div class="col-md-6">
                        <label class="form-label">{{ __('pharmacies.fields.phone') }}</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Area --}}
                    <div class="col-md-6">
                        <label class="form-label">{{ __('pharmacies.fields.area') }}</label>
                        <input type="text" name="area" class="form-control @error('area') is-invalid @enderror"
                               value="{{ old('area') }}">
                        @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Rep --}}
                    <div class="col-md-6">
                        <label class="form-label">{{ __('pharmacies.fields.rep') }}</label>
                        <select name="rep_id" class="form-select @error('rep_id') is-invalid @enderror">
                            <option value="">— {{ \App\Helpers\Helpers::translate('none') }} —</option>
                            @foreach($reps as $rep)
                                <option value="{{ $rep->id }}" {{ old('rep_id') == $rep->id ? 'selected' : '' }}>
                                    {{ $rep->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('rep_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Address --}}
                    <div class="col-12">
                        <label class="form-label">{{ __('pharmacies.fields.address') }}</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                               value="{{ old('address') }}">
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Credit Limit --}}
                    <div class="col-md-6">
                        <label class="form-label">{{ __('pharmacies.fields.credit_limit') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input type="number" step="0.01" min="0" name="credit_limit"
                                   class="form-control @error('credit_limit') is-invalid @enderror"
                                   value="{{ old('credit_limit', 0) }}">
                            @error('credit_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Opening Balance --}}
                    <div class="col-md-6">
                        <label class="form-label">{{ __('pharmacies.fields.opening_balance') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-wallet2"></i></span>
                            <input type="number" step="0.01" name="opening_balance"
                                   class="form-control @error('opening_balance') is-invalid @enderror"
                                   value="{{ old('opening_balance', 0) }}">
                            @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="col-12">
                        <label class="form-label">{{ __('pharmacies.fields.notes') }}</label>
                        <textarea name="notes" rows="2"
                                  class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Active --}}
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                   id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                {{ __('pharmacies.fields.is_active') }}
                            </label>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-save me-1"></i>{{ __('pharmacies.buttons.save') }}
                        </button>
                        <a href="{{ route('admin.pharmacies.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>{{ __('pharmacies.buttons.cancel') }}
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection

