<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProductController extends Controller
{
    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function authorizeProductAction($product, string $action): void
    {
        if (! Gate::allows($action, $product)) {
            throw new AccessDeniedHttpException(
                'You do not have permission to ' . $action . ' this product.'
            );
        }
    }

    /** Validation rules shared by store & update. */
    private function validationRules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0',
            'details'          => 'required|string',
            // 'quantity' removed — stock is managed via stock_movements, not this column.
            'company_id'       => 'nullable|exists:companies,id',
            'form'             => 'nullable|in:tablet,capsule,syrup,injection,cream,ointment,drops,spray,powder,gel,solution,suspension,other',
            'net_price_syp'    => 'required|numeric|min:0',
            'public_price_syp' => 'required|numeric|min:0',
        ];
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Product::with(['user', 'company', 'productPrice']);

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }

        $products  = $query->get();
        $users     = User::all();
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        // Batch aggregate — one query instead of one per product.
        $stockMap = StockMovement::whereIn('product_id', $products->pluck('id'))
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) as total')
            ->pluck('total', 'product_id');

        return view('admin.Products.index', compact('products', 'users', 'companies', 'stockMap'));
    }

    // ─── Export ───────────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $userId      = $request->input('user_id');
        $currentUser = Auth::user();

        $query = Product::with(['user', 'company', 'productPrice']);

        if ($currentUser->role === 'moderator') {
            $query->where('user_id', $currentUser->id);
        } elseif ($currentUser->role === 'admin' && $userId) {
            $query->where('user_id', $userId);
        }

        $products    = $query->get();

        // Resolve live stock from stock_movements (one batch query).
        $stockMap = StockMovement::whereIn('product_id', $products->pluck('id'))
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) as total')
            ->pluck('total', 'product_id');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['ID', 'Name', 'Price', 'Details', 'Quantity',
                    'Company', 'Form', 'Net Price SYP', 'Public Price SYP',
                    'Created By', 'Created At'];
        foreach ($headers as $i => $header) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $header);
        }
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);

        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->id);
            $sheet->setCellValue('B' . $row, $product->name);
            $sheet->setCellValue('C' . $row, $product->price);
            $sheet->setCellValue('D' . $row, $product->details);
            $sheet->setCellValue('E' . $row, (int) ($stockMap[$product->id] ?? 0));
            $sheet->setCellValue('F' . $row, $product->company?->name ?? '');
            $sheet->setCellValue('G' . $row, $product->form ?? '');
            $sheet->setCellValue('H' . $row, $product->productPrice?->net_price_syp ?? 0);
            $sheet->setCellValue('I' . $row, $product->productPrice?->public_price_syp ?? 0);
            $sheet->setCellValue('J' . $row, $product->user?->name ?? 'Unknown');
            $sheet->setCellValue('K' . $row, $product->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'products_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'products_export');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ─── Create / Store ───────────────────────────────────────────────────────

    public function create()
    {
        $this->authorizeProductAction(Product::class, 'create');

        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $this->authorizeProductAction(Product::class, 'create');

        $validated = $request->validate($this->validationRules());

        $product          = new Product(Arr::except($validated, ['net_price_syp', 'public_price_syp']));
        $product->user_id = Auth::id();
        $product->save();

        $product->productPrice()->create([
            'net_price_syp'    => $validated['net_price_syp'],
            'public_price_syp' => $validated['public_price_syp'],
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('success', \App\Helpers\Helpers::translate('product_created'));
    }

    // ─── Edit / Update ────────────────────────────────────────────────────────

    public function edit(int $id)
    {
        $product = Product::with('productPrice')->findOrFail($id);
        $this->authorizeProductAction($product, 'update');

        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'companies'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $this->authorizeProductAction($product, 'update');

        $validated = $request->validate($this->validationRules());

        $product->update(Arr::except($validated, ['net_price_syp', 'public_price_syp']));

        $product->productPrice()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'net_price_syp'    => $validated['net_price_syp'],
                'public_price_syp' => $validated['public_price_syp'],
            ]
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', \App\Helpers\Helpers::translate('product_updated'));
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $this->authorizeProductAction($product, 'delete');

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', \App\Helpers\Helpers::translate('product_deleted'));
    }

    // ─── Import ───────────────────────────────────────────────────────────────

    public function import()
    {
        return view('admin.products.import');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Headers — columns A-H
        $headers = ['Name', 'Price', 'Details', 'Quantity',
                    'Company', 'Form', 'Net Price SYP', 'Public Price SYP'];
        foreach ($headers as $i => $header) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $header);
        }
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        // Example row
        $example = ['Amoxicillin 500mg', '2500', 'Antibiotic capsules', '100',
                    'Pharma Co', 'Capsule', '2000', '2500'];
        foreach ($example as $i => $val) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '2', $val);
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'products_import_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'products_template');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $file        = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $rows        = $spreadsheet->getActiveSheet()->toArray();

            array_shift($rows); // remove header row

            $importCount = 0;
            $errors      = [];

            foreach ($rows as $index => $row) {
                // Skip fully empty rows (backward compat: old files only have 4 cols)
                if (empty($row[0]) && empty($row[1]) && empty($row[2]) && empty($row[3])) {
                    continue;
                }

                $rowNumber = $index + 2;

                // ── Required legacy columns ────────────────────────────────
                if (empty($row[0])) {
                    $errors[] = "Row {$rowNumber}: Name is required";
                    continue;
                }
                if (! is_numeric($row[1]) || $row[1] < 0) {
                    $errors[] = "Row {$rowNumber}: Price must be a positive number";
                    continue;
                }
                if (empty($row[2])) {
                    $errors[] = "Row {$rowNumber}: Details are required";
                    continue;
                }
                if (! is_numeric($row[3]) || $row[3] < 0) {
                    $errors[] = "Row {$rowNumber}: Quantity must be a positive integer";
                    continue;
                }

                // ── Resolve optional company (col E = index 4) ────────────
                $companyId = null;
                if (! empty($row[4])) {
                    $companyName = trim($row[4]);
                    $company     = Company::firstOrCreate(
                        ['name' => $companyName],
                        ['is_active' => true]
                    );
                    $companyId = $company->id;
                }

                // ── Optional new columns ───────────────────────────────────
                $form           = isset($row[5]) && ! empty($row[5]) ? trim($row[5]) : null;
                $netPriceSyp    = isset($row[6]) && is_numeric($row[6]) ? (float) $row[6] : 0;
                $publicPriceSyp = isset($row[7]) && is_numeric($row[7]) ? (float) $row[7] : 0;

                // ── Create product ─────────────────────────────────────────
                $product = new Product([
                    'name'       => $row[0],
                    'price'      => $row[1],
                    'details'    => $row[2],
                    'company_id' => $companyId,
                    'form'       => $form,
                ]);
                // quantity is a legacy column (stock_movements is the source of truth).
                // Import still populates it for backward compatibility with old Excel files.
                $product->quantity = (int) $row[3];
                $product->user_id  = Auth::id();
                $product->save();

                // ── Create price record ────────────────────────────────────
                $product->productPrice()->create([
                    'net_price_syp'    => $netPriceSyp,
                    'public_price_syp' => $publicPriceSyp,
                ]);

                $importCount++;
            }

            if (count($errors) > 0) {
                return redirect()
                    ->route('admin.products.import')
                    ->with('error', __('messages.import_partial', ['count' => $importCount]))
                    ->with('import_errors', $errors);
            }

            return redirect()
                ->route('admin.products.import')
                ->with('success', __('messages.import_success', ['count' => $importCount]));

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.products.import')
                ->with('error', __('messages.import_error', ['error' => $e->getMessage()]));
        }
    }
}

