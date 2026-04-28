<?php

namespace Tests\Feature\Api;

use App\Models\AccountEntry;
use App\Models\Company;
use App\Models\Order;
use App\Models\Payment;
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
 * Ten tests covering the ten requested behaviours:
 *  1.  Admin can login and get a Passport token
 *  2.  Rep can access their assigned pharmacies only
 *  3.  Rep can create an order for their assigned pharmacy
 *  4.  Rep CANNOT create an order for an unassigned pharmacy (403)
 *  5.  Confirming an order creates TYPE_SALE stock movements
 *  6.  Confirming an order creates a TYPE_DEBIT account entry
 *  7.  Cancelling a confirmed order creates TYPE_SALE_CANCEL movement
 *  8.  Recording a payment creates a TYPE_CREDIT account entry
 *  9.  Pharmacy statement balance is arithmetically correct
 * 10.  Unauthenticated requests return 401
 *
 * Passport::actingAs() is used for all protected-route tests so no real
 * OAuth clients/tokens are needed — only tests 1 & 2 exercise the full
 * auth flow.
 */
class PharmacyWarehouseTest extends TestCase
{
    use RefreshDatabase;

    // ── Shared fixtures ───────────────────────────────────────────────────────

    private User     $admin;
    private User     $rep1;
    private User     $rep2;
    private Product  $product;
    private Pharmacy $pharmacyRep1;   // assigned to rep1
    private Pharmacy $pharmacyRep2;   // assigned to rep2

    protected function setUp(): void
    {
        parent::setUp();

        // Generate Passport RSA keys (safe to call repeatedly — --force overwrites).
        $this->artisan('passport:keys', ['--force' => true]);

        // Seed a personal-access client so createToken() works inside tests.
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
     * Create the minimum data needed by every test.
     * Direct create() calls — no factories needed for correctness.
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

        // Opening stock: 100 units
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
    //  Test 1 — Admin can login via the API and receive a Passport token
    // ═════════════════════════════════════════════════════════════════════════

    public function test_admin_can_login_and_get_token(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'success',
                     'data' => ['token', 'name'],
                     'message',
                 ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 2 — Rep can access ONLY their assigned pharmacies
    // ═════════════════════════════════════════════════════════════════════════

    public function test_rep_sees_only_own_pharmacies(): void
    {
        Passport::actingAs($this->rep1);

        $response = $this->getJson('/api/v1/pharmacies');

        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name');

        // rep1's pharmacy is present
        $this->assertTrue($names->contains('صيدلية الأمل'), 'rep1 should see their own pharmacy');

        // rep2's pharmacy must NOT be visible
        $this->assertFalse($names->contains('صيدلية النور'), 'rep1 must not see rep2 pharmacy');
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 3 — Rep CAN create an order for their assigned pharmacy
    // ═════════════════════════════════════════════════════════════════════════

    public function test_rep_can_create_order_for_assigned_pharmacy(): void
    {
        Passport::actingAs($this->rep1);

        $response = $this->postJson('/api/v1/orders', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'discount'    => 0,
            'notes'       => 'طلبية تجريبية',
            'items'       => [[
                'product_id' => $this->product->id,
                'quantity'   => 5,
                'unit_price' => 350,
                'discount'   => 0,
            ]],
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
    //  Test 4 — Rep CANNOT create an order for an unassigned pharmacy
    // ═════════════════════════════════════════════════════════════════════════

    public function test_rep_cannot_create_order_for_unassigned_pharmacy(): void
    {
        // rep1 is trying to create an order for pharmacyRep2 (belongs to rep2)
        Passport::actingAs($this->rep1);

        $response = $this->postJson('/api/v1/orders', [
            'pharmacy_id' => $this->pharmacyRep2->id,
            'discount'    => 0,
            'items'       => [[
                'product_id' => $this->product->id,
                'quantity'   => 5,
                'unit_price' => 350,
                'discount'   => 0,
            ]],
        ]);

        $response->assertForbidden(); // 403

        $this->assertDatabaseMissing('orders', [
            'pharmacy_id' => $this->pharmacyRep2->id,
            'rep_id'      => $this->rep1->id,
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 5 — Confirming an order creates TYPE_SALE stock movements
    // ═════════════════════════════════════════════════════════════════════════

    public function test_confirming_order_creates_sale_stock_movements(): void
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

        // Net stock: 100 (opening) − 10 (sale) = 90
        $netStock = StockMovement::where('product_id', $this->product->id)->sum('quantity');
        $this->assertEquals(90, $netStock);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 6 — Confirming an order creates a TYPE_DEBIT account entry
    // ═════════════════════════════════════════════════════════════════════════

    public function test_confirming_order_creates_debit_account_entry(): void
    {
        Passport::actingAs($this->rep1);

        $order = $this->createPendingOrder(qty: 10, unitPrice: 350);  // total = 3500

        $this->postJson("/api/v1/orders/{$order->id}/confirm")->assertOk();

        $this->assertDatabaseHas('account_entries', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'order_id'    => $order->id,
            'type'        => AccountEntry::TYPE_DEBIT,
            'amount'      => '3500.00',
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 7 — Cancelling a confirmed order creates TYPE_SALE_CANCEL movement
    // ═════════════════════════════════════════════════════════════════════════

    public function test_cancelling_confirmed_order_creates_sale_cancel_movement(): void
    {
        Passport::actingAs($this->rep1);

        $order = $this->createPendingOrder(qty: 15);

        $this->postJson("/api/v1/orders/{$order->id}/confirm")->assertOk();
        $this->postJson("/api/v1/orders/{$order->id}/cancel")->assertOk();

        // TYPE_SALE_CANCEL movement with +15 must exist (positive — restores stock)
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type'       => StockMovement::TYPE_SALE_CANCEL,
            'quantity'   => 15,
        ]);

        // Net: 100 (opening) − 15 (sale) + 15 (sale_cancel) = 100
        $netStock = StockMovement::where('product_id', $this->product->id)->sum('quantity');
        $this->assertEquals(100, $netStock);

        // A TYPE_CREDIT entry must also reverse the original debit
        $this->assertDatabaseHas('account_entries', [
            'order_id' => $order->id,
            'type'     => AccountEntry::TYPE_CREDIT,
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 8 — Recording a payment creates a TYPE_CREDIT account entry
    // ═════════════════════════════════════════════════════════════════════════

    public function test_payment_creates_credit_account_entry(): void
    {
        Passport::actingAs($this->rep1);

        $this->postJson('/api/v1/payments', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'amount'      => 7000,
            'method'      => 'cash',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('payments', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'amount'      => 7000,
        ]);

        $this->assertDatabaseHas('account_entries', [
            'pharmacy_id' => $this->pharmacyRep1->id,
            'type'        => AccountEntry::TYPE_CREDIT,
            'amount'      => '7000.00',
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 9 — Pharmacy statement balance is arithmetically correct
    // ═════════════════════════════════════════════════════════════════════════

    public function test_pharmacy_statement_balance_is_correct(): void
    {
        Passport::actingAs($this->admin);

        // Build a known ledger for pharmacyRep1 (opening_balance = 0):
        //   Debit  entry  +12000  (order sale)
        //   Credit entry  - 5000  (payment)
        //   Expected balance = 0 + 12000 - 5000 = 7000

        AccountEntry::create([
            'pharmacy_id' => $this->pharmacyRep1->id,
            'type'        => AccountEntry::TYPE_DEBIT,
            'amount'      => 12000,
            'description' => 'Test sale',
            'entry_date'  => now()->toDateString(),
            'created_by'  => $this->admin->id,
        ]);

        AccountEntry::create([
            'pharmacy_id' => $this->pharmacyRep1->id,
            'type'        => AccountEntry::TYPE_CREDIT,
            'amount'      => 5000,
            'description' => 'Test payment',
            'entry_date'  => now()->toDateString(),
            'created_by'  => $this->admin->id,
        ]);

        $response = $this->getJson(
            "/api/v1/pharmacies/{$this->pharmacyRep1->id}/statement"
        );

        $response->assertOk()
                 ->assertJson(['success' => true]);

        // Use (float) cast because json_encode serialises 12000.0 as the integer 12000
        $data = $response->json('data');
        $this->assertEquals(12000.0, (float) $data['total_debit'],  'total_debit mismatch');
        $this->assertEquals(5000.0,  (float) $data['total_credit'], 'total_credit mismatch');
        $this->assertEquals(7000.0,  (float) $data['balance'],      'balance mismatch');

        // Entries list must contain both rows
        $this->assertCount(2, $response->json('data.entries'));
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Test 10 — Unauthenticated requests return 401
    // ═════════════════════════════════════════════════════════════════════════

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $endpoints = [
            ['GET',  '/api/v1/products'],
            ['GET',  '/api/v1/pharmacies'],
            ['GET',  '/api/v1/orders'],
            ['POST', '/api/v1/orders'],
            ['POST', '/api/v1/payments'],
            ['GET',  "/api/v1/pharmacies/{$this->pharmacyRep1->id}/statement"],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $this->json($method, $uri)->assertUnauthorized(); // 401
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Helper
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * POST a pending order for rep1's pharmacy and return the persisted Order.
     */
    private function createPendingOrder(int $qty = 5, float $unitPrice = 350): Order
    {
        $response = $this->postJson('/api/v1/orders', [
            'pharmacy_id' => $this->pharmacyRep1->id,
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
}

