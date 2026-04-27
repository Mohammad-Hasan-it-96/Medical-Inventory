<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
class PharmacyController extends Controller
{
    public function index(Request $request)
    {
        $query = Pharmacy::with('rep');
        if ($s = $request->input('search')) {
            $query->where(fn($q) => $q->where('name','like',"%$s%")->orWhere('phone','like',"%$s%")->orWhere('area','like',"%$s%"));
        }
        if ($request->filled('rep_id'))  $query->where('rep_id', $request->input('rep_id'));
        if ($request->filled('status'))  $query->where('is_active', $request->input('status'));
        $pharmacies = $query->orderBy('name')->paginate(20)->withQueryString();
        $reps = User::where('role','rep')->orderBy('name')->get();
        return view('admin.pharmacies.index', compact('pharmacies','reps'));
    }
    public function create()
    {
        $reps = User::where('role','rep')->orderBy('name')->get();
        return view('admin.pharmacies.create', compact('reps'));
    }
    public function store(Request $request)
    {
        $v = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:500',
            'area'            => 'nullable|string|max:255',
            'rep_id'          => 'nullable|exists:users,id',
            'credit_limit'    => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric',
            'notes'           => 'nullable|string',
        ]);
        $v['is_active'] = $request->boolean('is_active', true);
        Pharmacy::create($v);
        return redirect()->route('admin.pharmacies.index')->with('success', __('pharmacies.messages.created'));
    }
    public function show(Pharmacy $pharmacy)
    {
        $pharmacy->loadCount('orders')
                 ->loadSum('payments', 'amount');

        $recentOrders   = $pharmacy->orders()->latest()->take(10)->get();
        $recentPayments = $pharmacy->payments()->latest()->take(10)->get();

        return view('admin.pharmacies.show', compact('pharmacy', 'recentOrders', 'recentPayments'));
    }

    public function edit(Pharmacy $pharmacy)
    {
        $reps = User::where('role','rep')->orderBy('name')->get();
        return view('admin.pharmacies.edit', compact('pharmacy','reps'));
    }
    public function update(Request $request, Pharmacy $pharmacy)
    {
        $v = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:500',
            'area'            => 'nullable|string|max:255',
            'rep_id'          => 'nullable|exists:users,id',
            'credit_limit'    => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric',
            'notes'           => 'nullable|string',
        ]);
        $v['is_active'] = $request->boolean('is_active');
        $pharmacy->update($v);
        return redirect()->route('admin.pharmacies.index')->with('success', __('pharmacies.messages.updated'));
    }
    public function destroy(Pharmacy $pharmacy)
    {
        $pharmacy->delete();
        return redirect()->route('admin.pharmacies.index')->with('success', __('pharmacies.messages.deleted'));
    }
}
