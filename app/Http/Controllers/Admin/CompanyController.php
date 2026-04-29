<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $sortable  = ['name', 'phone', 'updated_at'];
        $orderBy   = in_array($request->input('order_by'), $sortable) ? $request->input('order_by') : 'name';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $perPage   = in_array((int) $request->input('per_page', 20), [10, 20, 50, 100])
            ? (int) $request->input('per_page', 20) : 20;

        $query = Company::query();

        if ($search = $request->input('search')) {
            $query->where(fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
            );
        }
        if ($search = $request->input('search_by_region')) {
            $query->where(fn($q) =>
                $q->where('address', 'like', "%{$search}%")
            );
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status'));
        }

        $companies = $query->orderBy($orderBy, $direction)->paginate($perPage)->withQueryString();

        return view('admin.companies.index', compact('companies', 'orderBy', 'direction', 'perPage'));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:companies,name',
            'phone'     => 'nullable|string|max:50',
            'address'   => 'nullable|string|max:255',
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Company::create($validated);

        return redirect()->route('admin.companies.index')
            ->with('success', \App\Helpers\Helpers::translate('company_created'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:companies,name,' . $company->id,
            'phone'     => 'nullable|string|max:50',
            'address'   => 'nullable|string|max:255',
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $company->update($validated);

        return redirect()->route('admin.companies.index')
            ->with('success', \App\Helpers\Helpers::translate('company_updated'));
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', \App\Helpers\Helpers::translate('company_deleted'));
    }
}
