<?php

namespace Tests\Feature\Api;

use App\Models\AccountEntry;
use App\Models\Company;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessClient;
use Tests\TestCase;

/**
 * Feature tests for the pharmacy warehouse API (v1).
 *
 * Passport::actingAs() is used for all protected-route tests so no real
 * OAuth clients/tokens are needed — only the login-endpoint test (#1)
 * installs Passport to exercise the full auth flow.
 */
class PharmacyWarehouseTest extends TestCase
{
    use RefreshDatabase;

    // ── Shared fixtures ───────────────────────────────────────────────────────

    private User     $admin;
    private User     $rep1;
    private User     $rep2;
    private Product  $product;
    private Pharmacy $pharmacyRep1;  // assigned to rep1
    private Pharmacy $pharmacyRep2;  // assigned to rep2

    protected function setUp(): void
    {
        parent::setUp();

        // Generate Passport RSA encryption keys into storage/ (no DB writes).
        // Safe to call every time — --force overwrites stale keys.
        $this->artisan('passport:keys', ['--force' => true]);

        // Insert a personal-access client directly into the already-migrated tables
        // (RefreshDatabase handles the schema; we only need the client row).
        $client = PassportClient::forceCreate([
            'user_id'                => null,
            'name'                   => 'Test Personal Access Client',
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

    /**
     * Create the minimum data needed by every test in one place.
     * Uses simple creates/inserts — no factories needed for correctness.
     */
    private function buildFixtures(): void
    {
        $this->admin = User::create([
            'name'     => 'مدير النظام',
            'email'    => 'admin@test.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        $this->rep1 = User::create([
            'name'     => 'أحمد المندوب',
            'email'    => 'rep1@test.com',
            'password' => Hash::make('password'),
            'role'     => 'rep',
        ]);

        $this->rep2 = User::create([
            'name'     => 'محمد المندوب',
            'email'    => 'rep2@test.com',
            'password' => Hash::make('password'),
            'role'     => 'rep',
        ]);

        $company = Company::create([
            'name'      => 'شركة تاميكو للأدوية',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name'       => 'باراسيتامول 500 مج',
            'barcode'    => '6912345000001',
            'unit'       => 'علبة',
            'form'       => 'أقراص',
            'details'    => 'مسكن للألم',
            'company_id' => $company->id,
            'price'      => 350,
            'quantity'   => 0,
            'min_stock'  => 10,
            'is_active'  => true,
            'user_id'    => $this->admin->id,
        ]);

        ProductPrice::create([
            'product_id'       => $this->product->id,
            'net_price_syp'    => 250,
            'public_price_syp' => 350,
        ]);

        // Opening stock: 100 units available
        StockMovement::create([
            'product_id' => $this->product->id,
            'type'       => 'opening',
            'quantity'   => 100,
            'created_by' => $this->admin->id,
        ]);

        $this->pharmacyRep1 = Pharmacy::create([
            'name'            => 'صيدلية الأمل',
            'phone'           => '0944100001',
            'rep_id'          => $this->rep1->id,
            'credit_limit'    => 500000,
            'opening_balance' => 0,
            'is_active'       => true,
        ]);

        $this->pharmacyRep2 = Pharmacy::create([
            'name'            => 'صيدلية النور',
            'phone'           => '0944100002',
            'rep_id'          => $this->rep2->id,
            'credit_limit'    => 300000,
            'opening_balance' => 0,
            'is_active'       => true,
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 1 — Rep can login and receive a Passport token
    // ═════════════════════════════════════════════════════════════════════════

    public function test_rep_can_login_and_get_token(): void
    {

        $response = $this->postJson('/api/login', [
            'email'    => 'rep1@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'success',
                     'data' => ['token', 'name'],
                     'message',
                 ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 2 — Rep can list products
    // ═════════════════════════════════════════════════════════════════════════

    public function test_rep_can_list_products(): void
    {
        Passport::actingAs($this->rep1);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.0.name', 'باراسيتامول 500 مج');
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 3 — Rep can list ONLY their assigned pharmacies
    // ═════════════════════════════════════════════════════════════════════════

    public function test_rep_sees_only_own_pharmacies(): void
    {
        Passport::actingAs($this->rep1);

        $response = $this->getJson('/api/v1/pharmacies');

        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name');

        // rep1's pharmacy is present
        $this->assertTrue($names->contains('صيدلية الأمل'));

        // rep2's pharmacy is NOT present
        $this->assertFalse($names->contains('صيدلية النور'));
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 4 — Rep can create an order for their pharmacy
    // ═════════════════════════════════════════════════════════════════════════

    public function test_rep_can_create_order(): void
    {
        Passport::actingAs($this->rep1);

        $response = $this->postJson('/api/v1/orders', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'discount'    => 0,
            'notes'       => 'طلبية تجريبية',
            'items'       => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 5,
                    'unit_price' => 350,
                    'discount'   => 0,
                ],
            ],
        ]);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.status', 'pending')
                 ->assertJsonPath('data.total', '1750.00');

        $this->assertDatabaseHas('orders', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'rep_id'      => $this->rep1->id,
            'status'      => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'quantity'   => 5,
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 5 — Confirming an order decreases stock via stock_movements
    // ═════════════════════════════════════════════════════════════════════════

    public function test_confirming_order_decreases_stock(): void
    {
        Passport::actingAs($this->rep1);

        $order = $this->createPendingOrder(qty: 10);

        $this->postJson("/api/v1/orders/{$order->id}/confirm")->assertOk();

        // A 'sale' movement with quantity -10 must have been created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type'       => 'sale',
            'quantity'   => -10,
        ]);

        // Net stock should now be 100 (opening) − 10 (sale) = 90
        $netStock = StockMovement::where('product_id', $this->product->id)->sum('quantity');
        $this->assertEquals(90, $netStock);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 6 — Confirming an order creates a debit account_entry
    // ═════════════════════════════════════════════════════════════════════════

    public function test_confirming_order_creates_debit_account_entry(): void
    {
        Passport::actingAs($this->rep1);

        $order = $this->createPendingOrder(qty: 10, unitPrice: 350);

        $this->postJson("/api/v1/orders/{$order->id}/confirm")->assertOk();

        $this->assertDatabaseHas('account_entries', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'order_id'    => $order->id,
            'type'        => 'debit',
            'amount'      => 3500.00,
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 7 — Rep can record a payment
    // ═════════════════════════════════════════════════════════════════════════

    public function test_rep_can_record_payment(): void
    {
        Passport::actingAs($this->rep1);

        $response = $this->postJson('/api/v1/payments', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'amount'      => 5000,
            'method'      => 'cash',
            'notes'       => 'دفعة نقدية',
        ]);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.amount', '5000.00')
                 ->assertJsonPath('data.method', 'cash');

        $this->assertDatabaseHas('payments', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'amount'      => 5000,
            'method'      => 'cash',
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 8 — Recording a payment creates a credit account_entry
    // ═════════════════════════════════════════════════════════════════════════

    public function test_payment_creates_credit_account_entry(): void
    {
        Passport::actingAs($this->rep1);

        $this->postJson('/api/v1/payments', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'amount'      => 7000,
            'method'      => 'bank',
        ])->assertOk();

        $this->assertDatabaseHas('account_entries', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'type'        => 'credit',
            'amount'      => 7000.00,
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 9 — Cancelling a confirmed order reverses stock
    // ═════════════════════════════════════════════════════════════════════════

    public function test_cancelling_confirmed_order_reverses_stock(): void
    {
        Passport::actingAs($this->rep1);

        $order = $this->createPendingOrder(qty: 15);

        $this->postJson("/api/v1/orders/{$order->id}/confirm")->assertOk();
        $this->postJson("/api/v1/orders/{$order->id}/cancel")->assertOk();

        // sale_cancel movement with +15 must exist
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type'       => 'sale_cancel',
            'quantity'   => 15,
        ]);

        // Net stock: 100 (opening) − 15 (sale) + 15 (sale_cancel) = 100
        $netStock = StockMovement::where('product_id', $this->product->id)->sum('quantity');
        $this->assertEquals(100, $netStock);

        // A credit entry should also have been created to reverse the debit
        $this->assertDatabaseHas('account_entries', [
            'order_id' => $order->id,
            'type'     => 'credit',
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 10 — Unauthenticated requests are rejected with 401
    // ═════════════════════════════════════════════════════════════════════════

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $endpoints = [
            ['GET',  '/api/v1/products'],
            ['GET',  '/api/v1/pharmacies'],
            ['GET',  '/api/v1/orders'],
            ['POST', '/api/v1/orders'],
            ['POST', '/api/v1/payments'],
            ['GET',  '/api/v1/rep/dashboard'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertUnauthorized(); // 401
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Helpers
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Create a pending order for rep1's pharmacy and return the Order model.
     */
    private function createPendingOrder(int $qty = 5, float $unitPrice = 350): Order
    {
        $response = $this->postJson('/api/v1/orders', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'discount'    => 0,
            'items'       => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => $qty,
                    'unit_price' => $unitPrice,
                    'discount'   => 0,
                ],
            ],
        ]);

        $response->assertOk();

        return Order::findOrFail($response->json('data.id'));
    }
}

