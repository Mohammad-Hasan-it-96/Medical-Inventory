<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
class StockMovementController extends Controller
{
    public function __construct(protected StockService $stockService) {}
    public function index(Request $request)
    {
        $sortable  = ['created_at', 'quantity', 'type'];
        $orderBy   = in_array($request->input('order_by'), $sortable) ? $request->input('order_by') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $perPage   = in_array((int) $request->input('per_page', 20), [10, 20, 50, 100])
            ? (int) $request->input('per_page', 20) : 20;

        $query = StockMovement::with(['product', 'creator'])->orderBy($orderBy, $direction);
        if ($request->filled('product_id')) $query->where('product_id', $request->input('product_id'));
        if ($request->filled('type'))       $query->where('type', $request->input('type'));
        if ($request->filled('date_from'))  $query->whereDate('created_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to'))    $query->whereDate('created_at', '<=', $request->input('date_to'));
        $movements = $query->paginate($perPage)->withQueryString();
        $products  = Product::orderBy('name')->get(['id', 'name']);
        $types     = [
            StockMovement::TYPE_OPENING,
            StockMovement::TYPE_PURCHASE,
            StockMovement::TYPE_SALE,
            StockMovement::TYPE_SALE_CANCEL,
            StockMovement::TYPE_ADJUSTMENT,
            StockMovement::TYPE_RETURN_IN,
            StockMovement::TYPE_RETURN_OUT,
        ];
        return view('admin.stock-movements.index', compact('movements', 'products', 'types', 'orderBy', 'direction', 'perPage'));
    }
    public function adjust(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|not_in:0',
            'notes'      => 'nullable|string|max:500',
        ]);
        try {
            $movement = $this->stockService->recordMovement([
                'product_id' => $data['product_id'],
                'type'       => StockMovement::TYPE_ADJUSTMENT,
                'quantity'   => $data['quantity'],
                'notes'      => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $productName = Product::find($data['product_id'])?->name ?? "#{$data['product_id']}";
            activity('stock_movements')
                ->causedBy(auth()->user())
                ->performedOn($movement)
                ->event('adjusted')
                ->withProperties(['quantity' => $data['quantity'], 'notes' => $data['notes'] ?? null])
                ->log("Stock adjusted for '{$productName}': {$data['quantity']}");

            return redirect()->route('admin.stock-movements.index')
                ->with('success', __('messages.adjustment_created'));
        } catch (ValidationException $e) {
            return redirect()->route('admin.stock-movements.index')
                ->withErrors($e->errors(), 'adjust')
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->route('admin.stock-movements.index')
                ->with('error', __('messages.adjustment_failed', ['error' => $e->getMessage()]))
                ->withInput();
        }
    }
}
