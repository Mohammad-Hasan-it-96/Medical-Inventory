<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['product','creator'])->latest();
        if ($request->filled('product_id')) $query->where('product_id', $request->input('product_id'));
        if ($request->filled('type'))       $query->where('type', $request->input('type'));
        if ($request->filled('date_from'))  $query->whereDate('created_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to'))    $query->whereDate('created_at', '<=', $request->input('date_to'));
        $movements = $query->paginate(25)->withQueryString();
        $products  = Product::orderBy('name')->get(['id','name']);
        $types     = StockMovement::distinct()->pluck('type');
        return view('admin.stock-movements.index', compact('movements','products','types'));
    }
}
