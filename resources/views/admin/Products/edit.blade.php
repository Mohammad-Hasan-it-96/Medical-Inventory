@extends('layouts.app')

@section('title', \App\Helpers\Helpers::translate('edit_product'))

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-body">
            <h5 class="card-title mb-4">{{\App\Helpers\Helpers::translate('edit_product')}}</h5>

            <form method="POST" action="{{ route('admin.products.update', $product->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    {{-- Name --}}
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{\App\Helpers\Helpers::translate('product_name')}}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Price (legacy) --}}
                    <div class="col-md-6">
                        <label for="price" class="form-label">{{\App\Helpers\Helpers::translate('price')}}</label>
                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror"
                               id="price" name="price" value="{{ old('price', $product->price) }}" required>
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Net price SYP --}}
                    <div class="col-md-6">
                        <label for="net_price_syp" class="form-label">{{\App\Helpers\Helpers::translate('net_price_syp')}}</label>
                        <input type="number" step="0.01" min="0"
                               class="form-control @error('net_price_syp') is-invalid @enderror"
                               id="net_price_syp" name="net_price_syp"
                               value="{{ old('net_price_syp', $product->productPrice?->net_price_syp ?? 0) }}" required>
                        @error('net_price_syp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Public price SYP --}}
                    <div class="col-md-6">
                        <label for="public_price_syp" class="form-label">{{\App\Helpers\Helpers::translate('public_price_syp')}}</label>
                        <input type="number" step="0.01" min="0"
                               class="form-control @error('public_price_syp') is-invalid @enderror"
                               id="public_price_syp" name="public_price_syp"
                               value="{{ old('public_price_syp', $product->productPrice?->public_price_syp ?? 0) }}" required>
                        @error('public_price_syp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Company --}}
                    <div class="col-md-6">
                        <label for="company_id" class="form-label">{{\App\Helpers\Helpers::translate('company')}}</label>
                        <select class="form-select @error('company_id') is-invalid @enderror"
                                id="company_id" name="company_id">
                            <option value="">— {{\App\Helpers\Helpers::translate('none')}} —</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ old('company_id', $product->company_id) == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Pharmaceutical form --}}
                    <div class="col-md-6">
                        <label for="form" class="form-label">{{\App\Helpers\Helpers::translate('pharmaceutical_form')}}</label>
                        <select class="form-select @error('form') is-invalid @enderror" id="form" name="form">
                            <option value="">— {{\App\Helpers\Helpers::translate('none')}} —</option>
                            @foreach(['tablet','capsule','syrup','injection','cream','ointment','drops','spray','powder','gel','solution','suspension','other'] as $f)
                                <option value="{{ $f }}" {{ old('form', $product->form) === $f ? 'selected' : '' }}>
                                    {{\App\Helpers\Helpers::translate('pharm_form_' . $f)}}
                                </option>
                            @endforeach
                        </select>
                        @error('form')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Quantity field removed — stock is now tracked via Stock Movements.
                         Use the Stock Movements page to record openings, purchases, or adjustments. --}}

                    {{-- Description --}}
                    <div class="col-12">
                        <label for="details" class="form-label">{{\App\Helpers\Helpers::translate('description')}}</label>
                        <textarea class="form-control @error('details') is-invalid @enderror"
                                  id="details" name="details" rows="3" required>{{ old('details', $product->details) }}</textarea>
                        @error('details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-save me-2"></i>{{\App\Helpers\Helpers::translate('update_product')}}
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>{{\App\Helpers\Helpers::translate('cancel')}}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

