<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['pharmacy','order','creator'])->latest();
        if ($request->filled('pharmacy_id')) $query->where('pharmacy_id', $request->input('pharmacy_id'));
        if ($request->filled('date_from'))   $query->whereDate('paid_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to'))     $query->whereDate('paid_at', '<=', $request->input('date_to'));
        $payments   = $query->paginate(25)->withQueryString();
        $pharmacies = Pharmacy::orderBy('name')->get(['id','name']);
        return view('admin.payments.index', compact('payments','pharmacies'));
    }
}
