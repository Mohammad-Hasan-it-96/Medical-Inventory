<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function index(Request $request)
    {
        $query = Payment::with(['pharmacy','order','creator'])->latest('paid_at');
        if ($request->filled('pharmacy_id')) $query->where('pharmacy_id', $request->input('pharmacy_id'));
        if ($request->filled('method'))      $query->where('method', $request->input('method'));
        if ($request->filled('date_from'))   $query->whereDate('paid_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to'))     $query->whereDate('paid_at', '<=', $request->input('date_to'));
        $payments   = $query->paginate(25)->withQueryString();
        $pharmacies = Pharmacy::orderBy('name')->get(['id','name']);
        return view('admin.payments.index', compact('payments','pharmacies'));
    }

    public function create()
    {
        $pharmacies = Pharmacy::orderBy('name')->get(['id','name']);
        $orders     = Order::with('pharmacy')
            ->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_PENDING, Order::STATUS_DRAFT])
            ->latest()
            ->get(['id','order_number','pharmacy_id','status','total']);
        return view('admin.payments.create', compact('pharmacies','orders'));
    }

    public function store(Request $request)
    {
        try {
            $this->paymentService->recordPayment($request->all(), auth()->id());
            return redirect()->route('admin.payments.index')
                ->with('success', __('messages.payment_created'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', __('messages.payment_failed', ['error' => $e->getMessage()]))->withInput();
        }
    }
}
