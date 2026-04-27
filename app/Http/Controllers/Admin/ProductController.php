<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProductController extends Controller
{
    /**
     * Check authorization via Gate and throw 403 on failure.
     */
    private function authorizeProductAction($product, string $action): void
    {
        if (! Gate::allows($action, $product)) {
            throw new AccessDeniedHttpException(
                'You do not have permission to ' . $action . ' this product.'
            );
        }
    }

    /**
     * Display a listing of products (admin Blade view).
     */
    public function index(Request $request)
    {
        $userId   = $request->input('user_id');
        $query    = Product::with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $products = $query->get();
        $users    = User::all();

        return view('admin.products.index', compact('products', 'users'));
    }

    /**
     * Export products to Excel (.xlsx) download.
     */
    public function export(Request $request)
    {
        $userId      = $request->input('user_id');
        $currentUser = Auth::user();

        $query = Product::with('user');

        if ($currentUser->role === 'moderator') {
            $query->where('user_id', $currentUser->id);
        } elseif ($currentUser->role === 'admin' && $userId) {
            $query->where('user_id', $userId);
        }

        $products    = $query->get();
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Price');
        $sheet->setCellValue('D1', 'Details');
        $sheet->setCellValue('E1', 'Quantity');
        $sheet->setCellValue('F1', 'Created By');
        $sheet->setCellValue('G1', 'Created At');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->id);
            $sheet->setCellValue('B' . $row, $product->name);
            $sheet->setCellValue('C' . $row, $product->price);
            $sheet->setCellValue('D' . $row, $product->details);
            $sheet->setCellValue('E' . $row, $product->quantity);
            $sheet->setCellValue('F' . $row, $product->user->name ?? 'Unknown');
            $sheet->setCellValue('G' . $row, $product->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'products_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'products_export');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Show the form to create a new product.
     */
    public function create()
    {
        $this->authorizeProductAction(Product::class, 'create');

        return view('admin.products.create');
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $this->authorizeProductAction(Product::class, 'create');

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'details'  => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $product          = new Product($validated);
        $product->user_id = Auth::id();
        $product->save();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form to edit an existing product.
     */
    public function edit(int $id)
    {
        $product = Product::findOrFail($id);
        $this->authorizeProductAction($product, 'update');

        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $this->authorizeProductAction($product, 'update');

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'price'    => 'required|numeric|min:0',
            'details'  => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete the specified product.
     */
    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $this->authorizeProductAction($product, 'delete');

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Show the import form.
     */
    public function import()
    {
        return view('admin.products.import');
    }

    /**
     * Download a blank Excel import template.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Price');
        $sheet->setCellValue('C1', 'Details');
        $sheet->setCellValue('D1', 'Quantity');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $sheet->setCellValue('A2', 'Example Product');
        $sheet->setCellValue('B2', '99.99');
        $sheet->setCellValue('C2', 'This is a sample product description.');
        $sheet->setCellValue('D2', '10');

        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'products_import_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'products_template');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Process the uploaded Excel file and import products.
     */
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
                if (empty($row[0]) && empty($row[1]) && empty($row[2]) && empty($row[3])) {
                    continue;
                }

                $rowNumber = $index + 2;

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
                if (! is_numeric($row[3]) || $row[3] < 0 || ! is_int((int) $row[3])) {
                    $errors[] = "Row {$rowNumber}: Quantity must be a positive integer";
                    continue;
                }

                $product          = new Product([
                    'name'     => $row[0],
                    'price'    => $row[1],
                    'details'  => $row[2],
                    'quantity' => (int) $row[3],
                ]);
                $product->user_id = Auth::id();
                $product->save();

                $importCount++;
            }

            if (count($errors) > 0) {
                return redirect()
                    ->route('admin.products.import')
                    ->with('error', 'Import completed with errors. ' . $importCount . ' products imported.')
                    ->with('import_errors', $errors);
            }

            return redirect()
                ->route('admin.products.import')
                ->with('success', 'Successfully imported ' . $importCount . ' products.');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.products.import')
                ->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }
}

