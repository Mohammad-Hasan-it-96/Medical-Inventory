<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}
    public function index(Request $request)
    {
        $sortable  = ['created_at', 'total', 'status', 'order_number'];
        $orderBy   = in_array($request->input('order_by'), $sortable) ? $request->input('order_by') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $perPage   = in_array((int) $request->input('per_page', 20), [10, 20, 50, 100])
            ? (int) $request->input('per_page', 20) : 20;

        $query = Order::with(['pharmacy', 'rep'])->orderBy($orderBy, $direction);
        if ($request->filled('search'))      $query->where('order_number', 'like', '%'.$request->input('search').'%');
        if ($request->filled('status'))      $query->where('status', $request->input('status'));
        if ($request->filled('rep_id'))      $query->where('rep_id', $request->input('rep_id'));
        if ($request->filled('pharmacy_id')) $query->where('pharmacy_id', $request->input('pharmacy_id'));
        if ($request->filled('date_from'))   $query->whereDate('created_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to'))     $query->whereDate('created_at', '<=', $request->input('date_to'));
        $orders     = $query->paginate($perPage)->withQueryString();
        $reps       = User::where('role', 'rep')->orderBy('name')->get(['id','name']);
        $pharmacies = Pharmacy::orderBy('name')->get(['id','name']);
        return view('admin.orders.index', compact('orders', 'reps', 'pharmacies', 'orderBy', 'direction', 'perPage'));
    }
    public function show(Order $order)
    {
        $order->load(['pharmacy', 'rep', 'orderItems.product']);
        return view('admin.orders.show', compact('order'));
    }
    public function confirm(Order $order)
    {
        if (Gate::denies('confirm', $order)) {
            abort(403);
        }
        try {
            $this->orderService->confirmOrder($order, auth()->id());
            return redirect()->route('admin.orders.show', $order)
                ->with('success', __('messages.order_confirmed'));
        } catch (ValidationException $e) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', __('messages.order_confirm_failed', [
                    'error' => collect($e->errors())->flatten()->first(),
                ]));
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', __('messages.order_confirm_failed', ['error' => $e->getMessage()]));
        }
    }
    public function cancel(Order $order)
    {
        if (Gate::denies('cancel', $order)) {
            abort(403);
        }
        try {
            $this->orderService->cancelOrder($order, auth()->id());
            return redirect()->route('admin.orders.show', $order)
                ->with('success', __('messages.order_cancelled'));
        } catch (ValidationException $e) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', __('messages.order_cancel_failed', [
                    'error' => collect($e->errors())->flatten()->first(),
                ]));
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', __('messages.order_cancel_failed', ['error' => $e->getMessage()]));
        }
    }
}
