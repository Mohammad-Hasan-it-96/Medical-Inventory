@extends('layouts.app')

@section('title', \App\Helpers\Helpers::translate('create_product'))

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-body">
            <h5 class="card-title mb-4">{{\App\Helpers\Helpers::translate('add_new_product')}}</h5>
            
            <form method="POST" action="{{ route('admin.products.store') }}">
                @csrf
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{\App\Helpers\Helpers::translate('product_name')}}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="price" class="form-label">{{\App\Helpers\Helpers::translate('price')}}</label>
                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                               id="price" name="price" value="{{ old('price') }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="details" class="form-label">{{\App\Helpers\Helpers::translate('description')}}</label>
                        <textarea class="form-control @error('details') is-invalid @enderror" 
                                  id="details" name="details" rows="3" required>{{ old('details') }}</textarea>
                        @error('details')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="quantity" class="form-label">{{\App\Helpers\Helpers::translate('quantity')}}</label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" name="quantity" value="{{ old('quantity') }}" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-save me-2"></i>{{\App\Helpers\Helpers::translate('save_product')}}
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