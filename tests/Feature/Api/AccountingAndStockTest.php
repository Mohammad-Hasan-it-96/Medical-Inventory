<?php

namespace Tests\Feature\Api;

use App\Models\AccountEntry;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessClient;
use Tests\TestCase;

/**
 * Feature tests targeting accounting correctness and stock integrity.
 *
 *  1.  Confirming order with total > 0 creates exactly one debit account entry
 *  2.  Confirming order with total = 0 does NOT create any account entry
 *  3.  Cancelling a confirmed order (total > 0) creates a credit reversal entry
 *  4.  Cancelling a confirmed order (total = 0) does NOT create a credit entry
 *  5.  Payment cannot be linked to a pending order (422)
 *  6.  Payment cannot be linked to a draft order (422)
 *  7.  Payment CAN be linked to a confirmed order (200)
 *  8.  with_stock=1 products endpoint returns correct stock counts (batch query)
 *  9.  Confirming an order blocks when stock is insufficient
 * 10.  Confirming an order with multiple items checks every item's stock
 */
class AccountingAndStockTest extends TestCase
{
    use RefreshDatabase;

    private User     $admin;
    private User     $rep;
    private Product  $product;
    private Pharmacy $pharmacy;

    // ── Setup ─────────────────────────────────────────────────────────────────

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('passport:keys', ['--force' => true]);

        $client = PassportClient::forceCreate([
            'user_id'                => null,
            'name'                   => 'Test PAC',
            'secret'                 => Str::random(40),
            'provider'               => null,
            'redirect'               => '',
            'personal_access_client' => true,
            'password_client'        => false,
            'revoked'                => false,
        ]);
        PersonalAccessClient::forceCreate(['client_id' => $client->id]);

        $this->buildFixtures();
    }

    private function buildFixtures(): void
    {
        $this->admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@acct.test',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        $this->rep = User::create([
            'name'     => 'Rep',
            'email'    => 'rep@acct.test',
            'password' => Hash::make('password'),
            'role'     => 'rep',
        ]);

        $company = Company::create(['name' => 'Test Co', 'is_active' => true]);

        $this->product = Product::create([
            'name'       => 'Test Product',
            'barcode'    => 'TEST0001',
            'unit'       => 'box',
            'form'       => 'tablet',
            'details'    => 'test',
            'company_id' => $company->id,
            'price'      => 0,
            'min_stock'  => 0,
            'is_active'  => true,
            'user_id'    => $this->admin->id,
        ]);

        ProductPrice::create([
            'product_id'       => $this->product->id,
            'net_price_syp'    => 500,
            'public_price_syp' => 700,
        ]);

        // Opening stock: 200 units
        StockMovement::create([
            'product_id' => $this->product->id,
            'type'       => StockMovement::TYPE_OPENING,
            'quantity'   => 200,
            'created_by' => $this->admin->id,
        ]);

        $this->pharmacy = Pharmacy::create([
            'name'            => 'Test Pharmacy',
            'phone'           => '0900000001',
            'rep_id'          => $this->rep->id,
            'credit_limit'    => 1_000_000,
            'opening_balance' => 0,
            'is_active'       => true,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Create a pending order via the API and return the Order model.
     */
    private function createPendingOrder(int $qty = 10, float $unitPrice = 500): Order
    {
        Passport::actingAs($this->rep);

        $response = $this->postJson('/api/v1/orders', [
            'pharmacy_id' => $this->pharmacy->id,
            'discount'    => 0,
            'items'       => [[
                'product_id' => $this->product->id,
                'quantity'   => $qty,
                'unit_price' => $unitPrice,
                'discount'   => 0,
            ]],
        ]);

        $response->assertOk();

        return Order::findOrFail($response->json('data.id'));
    }

    /**
     * Build a pending order directly in the DB (bypasses API) for precise total control.
     * Useful when the total must be exactly 0.
     */
    private function buildOrderDirectly(float $unitPrice, int $qty = 1, string $status = Order::STATUS_PENDING): Order
    {
        $lineTotal = $unitPrice * $qty;

        $order = Order::create([
            'pharmacy_id'  => $this->pharmacy->id,
            'rep_id'       => $this->rep->id,
            'order_number' => 'ORD-TEST-' . rand(10000, 99999),
            'status'       => $status,
            'subtotal'     => $lineTotal,
            'discount'     => 0,
            'total'        => $lineTotal,
            'created_by'   => $this->admin->id,
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $this->product->id,
            'quantity'   => $qty,
            'unit_price' => $unitPrice,
            'discount'   => 0,
            'total'      => $lineTotal,
        ]);

        return $order;
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  1 — Confirming order with total > 0 creates one TYPE_DEBIT entry
    // ═════════════════════════════════════════════════════════════════════════

    public function test_confirming_order_with_positive_total_creates_debit_entry(): void
    {
        Passport::actingAs($this->rep);

        // 10 units × 500 = 5 000
        $order = $this->createPendingOrder(qty: 10, unitPrice: 500);
        $this->assertEquals(5000.00, (float) $order->total);

        $this->postJson("/api/v1/orders/{$order->id}/confirm")->assertOk();

        // Exactly one debit entry for this order
        $this->assertDatabaseHas('account_entries', [
            'pharmacy_id' => $this->pharmacy->id,
            'order_id'    => $order->id,
            'type'        => AccountEntry::TYPE_DEBIT,
            'amount'      => '5000.00',
        ]);

        $this->assertEquals(
            1,
            AccountEntry::where('order_id', $order->id)
                        ->where('type', AccountEntry::TYPE_DEBIT)
                        ->count(),
            'Expected exactly 1 debit entry for the order'
        );
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  2 — Confirming order with total = 0 does NOT create any account entry
    // ═════════════════════════════════════════════════════════════════════════

    public function test_confirming_zero_total_order_creates_no_account_entry(): void
    {
        Passport::actingAs($this->admin);

        // Build an order where unit_price = 0 so total = 0.
        $order = $this->buildOrderDirectly(unitPrice: 0.00, qty: 5);

        $this->postJson("/api/v1/orders/{$order->id}/confirm")->assertOk();

        $this->assertEquals(
            0,
            AccountEntry::where('order_id', $order->id)->count(),
            'Zero-total order must not generate any account entry'
        );
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  3 — Cancelling confirmed order (total > 0) posts credit reversal
    // ═════════════════════════════════════════════════════════════════════════

    public function test_cancelling_confirmed_order_with_positive_total_creates_credit_reversal(): void
    {
        Passport::actingAs($this->rep);

        $order = $this->createPendingOrder(qty: 5, unitPrice: 500); // total = 2500

        $this->postJson("/api/v1/orders/{$order->id}/confirm")->assertOk();
        $this->postJson("/api/v1/orders/{$order->id}/cancel")->assertOk();

        // Debit was posted on confirm, credit reversal must exist after cancel
        $this->assertDatabaseHas('account_entries', [
            'order_id' => $order->id,
            'type'     => AccountEntry::TYPE_DEBIT,
            'amount'   => '2500.00',
        ]);
        $this->assertDatabaseHas('account_entries', [
            'order_id' => $order->id,
            'type'     => AccountEntry::TYPE_CREDIT,
            'amount'   => '2500.00',
        ]);

        // Net accounting impact on pharmacy = 0 (debit + credit cancel each other)
        $net = AccountEntry::where('pharmacy_id', $this->pharmacy->id)
            ->selectRaw("SUM(CASE WHEN type='debit' THEN amount ELSE -amount END) as net")
            ->value('net');

        $this->assertEquals(0.00, (float) $net, 'Net ledger impact after confirm+cancel must be zero');
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  4 — Cancelling confirmed order (total = 0) does NOT create credit entry
    // ═════════════════════════════════════════════════════════════════════════

    public function test_cancelling_confirmed_zero_total_order_creates_no_credit_entry(): void
    {
        Passport::actingAs($this->admin);

        $order = $this->buildOrderDirectly(unitPrice: 0.00, qty: 3);

        $this->postJson("/api/v1/orders/{$order->id}/confirm")->assertOk();
        $this->postJson("/api/v1/orders/{$order->id}/cancel")->assertOk();

        $this->assertEquals(
            0,
            AccountEntry::where('order_id', $order->id)->count(),
            'Zero-total confirmed+cancelled order must produce no account entries at all'
        );
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  5 — Payment cannot be linked to a PENDING order (422)
    // ═════════════════════════════════════════════════════════════════════════

    public function test_payment_cannot_link_to_pending_order(): void
    {
        Passport::actingAs($this->rep);

        $order = $this->createPendingOrder();
        $this->assertEquals(Order::STATUS_PENDING, $order->status);

        $response = $this->postJson('/api/v1/payments', [
            'pharmacy_id' => $this->pharmacy->id,
            'amount'      => 100,
            'method'      => 'cash',
            'order_id'    => $order->id,
        ]);

        $response->assertUnprocessable(); // 422

        // The API wraps validation errors inside 'data', not 'errors'.
        // PaymentController::store() → sendError('Validation failed.', $e->errors(), 422)
        $this->assertArrayHasKey(
            'order_id',
            $response->json('data') ?? [],
            'Expected order_id validation error in response data'
        );

        // No payment row must be created
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  6 — Payment cannot be linked to a DRAFT order (422)
    // ═════════════════════════════════════════════════════════════════════════

    public function test_payment_cannot_link_to_draft_order(): void
    {
        Passport::actingAs($this->admin);

        $order = $this->buildOrderDirectly(unitPrice: 500, qty: 2, status: Order::STATUS_DRAFT);
        $this->assertEquals(Order::STATUS_DRAFT, $order->status);

        $response = $this->postJson('/api/v1/payments', [
            'pharmacy_id' => $this->pharmacy->id,
            'amount'      => 100,
            'method'      => 'cash',
            'order_id'    => $order->id,
        ]);

        $response->assertUnprocessable(); // 422
        $this->assertArrayHasKey(
            'order_id',
            $response->json('data') ?? [],
            'Expected order_id validation error in response data'
        );
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  7 — Payment CAN be linked to a CONFIRMED order (200)
    // ═════════════════════════════════════════════════════════════════════════

    public function test_payment_can_link_to_confirmed_order(): void
    {
        Passport::actingAs($this->rep);

        $order = $this->createPendingOrder(qty: 4, unitPrice: 500); // total = 2000

        $this->postJson("/api/v1/orders/{$order->id}/confirm")->assertOk();
        $order->refresh();
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->status);

        $response = $this->postJson('/api/v1/payments', [
            'pharmacy_id' => $this->pharmacy->id,
            'amount'      => 2000,
            'method'      => 'cash',
            'order_id'    => $order->id,
        ]);

        $response->assertOk()
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('payments', [
            'pharmacy_id' => $this->pharmacy->id,
            'order_id'    => $order->id,
            'amount'      => '2000.00',
        ]);

        // Payment must have created a credit entry
        $this->assertDatabaseHas('account_entries', [
            'pharmacy_id' => $this->pharmacy->id,
            'order_id'    => $order->id,
            'type'        => AccountEntry::TYPE_CREDIT,
            'amount'      => '2000.00',
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  8 — with_stock=1 returns correct batch-calculated stock
    // ═════════════════════════════════════════════════════════════════════════

    public function test_products_with_stock_returns_correct_batch_stock(): void
    {
        Passport::actingAs($this->admin);

        // Create a second product with known stock.
        $product2 = Product::create([
            'name'      => 'Second Product',
            'barcode'   => 'TEST0002',
            'unit'      => 'box',
            'form'      => 'capsule',
            'details'   => 'test',
            'price'     => 0,
            'min_stock' => 0,
            'is_active' => true,   // must be true — ProductController uses ->active() scope
            'user_id'   => $this->admin->id,
        ]);

        StockMovement::create([
            'product_id' => $product2->id,
            'type'       => StockMovement::TYPE_OPENING,
            'quantity'   => 75,
            'created_by' => $this->admin->id,
        ]);

        // Add a sale on product1 to verify the batch aggregates correctly
        StockMovement::create([
            'product_id' => $this->product->id,
            'type'       => StockMovement::TYPE_SALE,
            'quantity'   => -50,
            'created_by' => $this->admin->id,
        ]);
        // product1 net: 200 - 50 = 150, product2 net: 75

        // Count queries during the request — there should be NO per-product SUM queries.
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });

        $response = $this->getJson('/api/v1/products?with_stock=1');

        $response->assertOk();

        // When ResourceCollection is serialized inside sendResponse(['data' => $collection]),
        // jsonSerialize() is called (not toResponse()), so items are flat at json('data'),
        // NOT at json('data.data') which would be the paginated toResponse() structure.
        $items = collect($response->json('data'));

        $p1 = $items->firstWhere('id', $this->product->id);
        $p2 = $items->firstWhere('id', $product2->id);

        $this->assertNotNull($p1, 'Product 1 should be in the response');
        $this->assertNotNull($p2, 'Product 2 should be in the response');

        $this->assertEquals(150, $p1['current_stock'], 'Product 1 stock should be 150');
        $this->assertEquals(75,  $p2['current_stock'], 'Product 2 stock should be 75');

        // The batch query means the total number of DB queries should be
        // well below one-per-product (2 products + 1 stock query, not 2 stock queries).
        // We assert a reasonable upper bound: pagination + products + stock = ~5 queries max.
        $this->assertLessThanOrEqual(
            10,
            $queryCount,
            "Expected at most 10 queries for with_stock=1 (actual: {$queryCount}). Possible N+1 regression."
        );
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  9 — Confirming an order is blocked when stock is insufficient
    // ═════════════════════════════════════════════════════════════════════════

    public function test_confirm_is_blocked_when_stock_is_insufficient(): void
    {
        Passport::actingAs($this->rep);

        // Available: 200. Order requests 201.
        $order = $this->createPendingOrder(qty: 201, unitPrice: 500);

        $response = $this->postJson("/api/v1/orders/{$order->id}/confirm");

        $response->assertUnprocessable(); // 422
        $response->assertJsonPath('success', false);

        // Order must still be pending
        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => Order::STATUS_PENDING,
        ]);

        // No stock movement must have been created
        $this->assertDatabaseMissing('stock_movements', [
            'type'       => StockMovement::TYPE_SALE,
            'product_id' => $this->product->id,
        ]);

        // No account entry must have been created
        $this->assertDatabaseMissing('account_entries', ['order_id' => $order->id]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 10 — Batch stock check blocks on ANY insufficient item
    // ═════════════════════════════════════════════════════════════════════════

    public function test_confirm_batch_stock_check_blocks_if_any_item_is_insufficient(): void
    {
        Passport::actingAs($this->admin);

        // Create a second product with only 5 units available
        $scarce = Product::create([
            'name'      => 'Scarce Product',
            'barcode'   => 'SCARCE001',
            'unit'      => 'vial',
            'form'      => 'injection',
            'details'   => 'limited',
            'price'     => 0,
            'min_stock' => 0,
            'is_active' => true,
            'user_id'   => $this->admin->id,
        ]);

        StockMovement::create([
            'product_id' => $scarce->id,
            'type'       => StockMovement::TYPE_OPENING,
            'quantity'   => 5,
            'created_by' => $this->admin->id,
        ]);

        // Build a two-item order: product1 (200 available, order 10 ✅) + scarce (5 available, order 99 ❌)
        $order = Order::create([
            'pharmacy_id'  => $this->pharmacy->id,
            'rep_id'       => $this->rep->id,
            'order_number' => 'ORD-MULTI-001',
            'status'       => Order::STATUS_PENDING,
            'subtotal'     => 10 * 500 + 99 * 100,
            'discount'     => 0,
            'total'        => 10 * 500 + 99 * 100,
            'created_by'   => $this->admin->id,
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $this->product->id,
            'quantity'   => 10,
            'unit_price' => 500,
            'discount'   => 0,
            'total'      => 5000,
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $scarce->id,
            'quantity'   => 99,   // insufficient (only 5 available)
            'unit_price' => 100,
            'discount'   => 0,
            'total'      => 9900,
        ]);

        $response = $this->postJson("/api/v1/orders/{$order->id}/confirm");

        $response->assertUnprocessable(); // 422

        // Neither product must have a sale movement — transaction rolled back
        $this->assertDatabaseMissing('stock_movements', [
            'type'       => StockMovement::TYPE_SALE,
            'product_id' => $this->product->id,
        ]);
        $this->assertDatabaseMissing('stock_movements', [
            'type'       => StockMovement::TYPE_SALE,
            'product_id' => $scarce->id,
        ]);

        // No account entry
        $this->assertDatabaseMissing('account_entries', ['order_id' => $order->id]);

        // Order still pending
        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => Order::STATUS_PENDING,
        ]);
    }
}

