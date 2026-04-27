<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['pharmacy','rep'])->latest();
        if ($request->filled('status'))      $query->where('status', $request->input('status'));
        if ($request->filled('rep_id'))      $query->where('rep_id', $request->input('rep_id'));
        if ($request->filled('pharmacy_id')) $query->where('pharmacy_id', $request->input('pharmacy_id'));
        if ($request->filled('date_from'))   $query->whereDate('created_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to'))     $query->whereDate('created_at', '<=', $request->input('date_to'));
        $orders     = $query->paginate(20)->withQueryString();
        $reps       = User::where('role','rep')->orderBy('name')->get();
        $pharmacies = Pharmacy::orderBy('name')->get();
        return view('admin.orders.index', compact('orders','reps','pharmacies'));
    }
    public function show(Order $order)
    {
        $order->load(['pharmacy','rep','orderItems.product']);
        return view('admin.orders.show', compact('order'));
    }
}
