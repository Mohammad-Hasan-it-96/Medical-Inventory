# Medical Inventory — API v1 Reference

> **Base URL** `http://your-server.com/api`  
> **Flutter package** `dio` or `http`  
> **Content-Type** `application/json` for all requests and responses  
> **Auth scheme** Laravel Passport — Bearer token in `Authorization` header  

---

## Table of Contents

1. [Response Envelope](#response-envelope)
2. [Authentication — Login](#1-authentication--login)
3. [Sync — Bootstrap](#2-sync--bootstrap)
4. [Sync — Incremental Changes](#3-sync--incremental-changes)
5. [Products — List](#4-products--list)
6. [Pharmacies — List](#5-pharmacies--list)
7. [Orders — Create](#6-orders--create)
8. [Orders — List](#7-orders--list)
9. [Orders — Show](#8-orders--show)
10. [Orders — Confirm](#9-orders--confirm)
11. [Orders — Cancel](#10-orders--cancel)
12. [Payments — Create](#11-payments--create)
13. [Pharmacy Statement](#12-pharmacy-statement)
14. [Product Stock](#13-product-stock)

---

## Response Envelope

Every endpoint returns the same outer envelope.

### Success
```json
{
  "success": true,
  "data": { ... },
  "message": "Human-readable description"
}
```

### Error
```json
{
  "success": false,
  "message": "Error description",
  "data": {
    "field_name": ["Validation message"]
  }
}
```

> `data` is **omitted** from error responses when there are no field-level messages.

### Paginated collections

When a list endpoint returns a paginator, `data` contains an inner `data` array plus metadata:

```json
{
  "success": true,
  "data": {
    "data": [ { ... }, { ... } ],
    "current_page": 1,
    "last_page": 4,
    "per_page": 15,
    "total": 56
  },
  "message": "Items retrieved successfully."
}
```

### Common HTTP status codes

| Code | Meaning |
|------|---------|
| 200  | Success |
| 401  | Unauthenticated — invalid or missing token |
| 403  | Forbidden — authenticated but not allowed |
| 404  | Resource not found |
| 422  | Validation / business-rule failure |
| 500  | Server error |

---

## 1. Authentication — Login

Obtain a Passport bearer token. The token has no TTL — persist it in Flutter secure storage.

**`POST /api/login`** — public, no auth required

### Request body

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `email` | string | ✅ | |
| `password` | string | ✅ | |

```json
{
  "email": "rep1@example.com",
  "password": "password"
}
```

### Success response `200`

```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYWJj...",
    "name": "أحمد الحسن"
  },
  "message": "User login successfully."
}
```

> Store `data.token` and send it as `Authorization: Bearer <token>` on all subsequent requests.

### Error response `401`

```json
{
  "success": false,
  "message": "Unauthorised.",
  "data": {
    "error": "Unauthorised"
  }
}
```

---

## 2. Sync — Bootstrap

Download the full initial dataset for a freshly-installed app.  
Call this **once** on first launch, then use the `/changes` endpoint for incremental updates.

**`GET /api/v1/sync/bootstrap`** — 🔒 auth required

### Query parameters

| Param | Type | Default | Notes |
|-------|------|---------|-------|
| `per_page` | integer | 100 | Max 500 per section |
| `companies_page` | integer | 1 | Page for companies section |
| `products_page` | integer | 1 | Page for products section |
| `pharmacies_page` | integer | 1 | Page for pharmacies section |

### Success response `200`

```json
{
  "success": true,
  "data": {
    "server_time": "2026-04-28T08:30:00+00:00",
    "current_user": {
      "id": 2,
      "name": "أحمد الحسن",
      "email": "rep1@example.com",
      "role": "rep",
      "profile_picture": "http://your-server.com/images/default-avatar.png"
    },
    "companies": {
      "data": [
        {
          "id": 1,
          "name": "شركة تاميكو للأدوية",
          "phone": "011-2345678",
          "address": "دمشق - المزة - طريق المطار",
          "is_active": true,
          "updated_at": "2026-04-20T10:00:00+00:00",
          "deleted_at": null
        }
      ],
      "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 100,
        "total": 5
      }
    },
    "products": {
      "data": [
        {
          "id": 1,
          "name": "باراسيتامول 500 مج",
          "barcode": "6912345000001",
          "unit": "علبة",
          "form": "أقراص",
          "net_price_syp": "250.00",
          "public_price_syp": "350.00",
          "is_active": true,
          "updated_at": "2026-04-20T10:00:00+00:00",
          "deleted_at": null
        }
      ],
      "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 100,
        "total": 30
      }
    },
    "pharmacies": {
      "data": [
        {
          "id": 1,
          "name": "صيدلية الأمل",
          "phone": "0944100001",
          "address": "دمشق - المزة",
          "area": "المزة",
          "credit_limit": "500000.00",
          "opening_balance": "0.00",
          "rep": {
            "id": 2,
            "name": "أحمد الحسن"
          },
          "is_active": true,
          "updated_at": "2026-04-20T10:00:00+00:00",
          "deleted_at": null
        }
      ],
      "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 100,
        "total": 10
      }
    }
  },
  "message": "Bootstrap data retrieved successfully."
}
```

> **Rep scoping** — a `rep` user only receives pharmacies assigned to them.  
> **Save `server_time`** — pass it as `updated_after` to the `/changes` endpoint on the next sync.

---

## 3. Sync — Incremental Changes

Fetch only records that have been created, updated, or deleted since the last sync.

**`GET /api/v1/sync/changes`** — 🔒 auth required

### Query parameters

| Param | Type | Required | Notes |
|-------|------|----------|-------|
| `updated_after` | ISO-8601 datetime | ✅ | Use `server_time` from previous sync |
| `per_page` | integer | 100 | Max 500 per section |
| `companies_page` | integer | 1 | |
| `products_page` | integer | 1 | |
| `product_prices_page` | integer | 1 | |
| `pharmacies_page` | integer | 1 | |

### Success response `200`

```json
{
  "success": true,
  "data": {
    "server_time": "2026-04-28T09:15:00+00:00",
    "companies": {
      "data": [],
      "meta": { "current_page": 1, "last_page": 1, "per_page": 100, "total": 0 }
    },
    "products": {
      "data": [
        {
          "id": 7,
          "name": "أزيثرومايسين 500 مج",
          "barcode": "6912345000007",
          "unit": "علبة",
          "form": "أقراص",
          "net_price_syp": "900.00",
          "public_price_syp": "1300.00",
          "is_active": true,
          "updated_at": "2026-04-28T09:00:00+00:00",
          "deleted_at": null
        }
      ],
      "meta": { "current_page": 1, "last_page": 1, "per_page": 100, "total": 1 }
    },
    "product_prices": {
      "data": [],
      "meta": { "current_page": 1, "last_page": 1, "per_page": 100, "total": 0 }
    },
    "pharmacies": {
      "data": [],
      "meta": { "current_page": 1, "last_page": 1, "per_page": 100, "total": 0 }
    }
  },
  "message": "Changes retrieved successfully."
}
```

> Records with `deleted_at != null` have been **soft-deleted** — remove them from your local store.

### Error response `422` — missing `updated_after`

```json
{
  "success": false,
  "message": "The updated after field is required.",
  "data": {
    "updated_after": ["The updated after field is required."]
  }
}
```

---

## 4. Products — List

**`GET /api/v1/products`** — 🔒 auth required

### Query parameters

| Param | Type | Notes |
|-------|------|-------|
| `search` | string | Match on name or barcode |
| `company_id` | integer | Filter by manufacturer |
| `updated_after` | datetime | Incremental sync |
| `with_stock` | `1` / `0` | Append `current_stock` field (adds 1 DB query per item) |
| `page` | integer | Default 1, 15 items per page |

### Success response `200`

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "باراسيتامول 500 مج",
        "barcode": "6912345000001",
        "unit": "علبة",
        "form": "أقراص",
        "company": {
          "id": 1,
          "name": "شركة تاميكو للأدوية",
          "is_active": true
        },
        "net_price_syp": "250.00",
        "public_price_syp": "350.00",
        "current_stock": 480,
        "is_active": true,
        "updated_at": "2026-04-20T10:00:00+00:00",
        "deleted_at": null
      },
      {
        "id": 2,
        "name": "إيبوبروفين 400 مج",
        "barcode": "6912345000002",
        "unit": "علبة",
        "form": "أقراص",
        "company": {
          "id": 1,
          "name": "شركة تاميكو للأدوية",
          "is_active": true
        },
        "net_price_syp": "300.00",
        "public_price_syp": "420.00",
        "current_stock": 300,
        "is_active": true,
        "updated_at": "2026-04-20T10:00:00+00:00",
        "deleted_at": null
      }
    ],
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 30
  },
  "message": "Products retrieved successfully."
}
```

> `current_stock` is only present when `?with_stock=1` is passed.  
> `net_price_syp` and `public_price_syp` are only present when the product has a price record.

---

## 5. Pharmacies — List

**`GET /api/v1/pharmacies`** — 🔒 auth required

> **Rep scoping** — `rep` users only see pharmacies assigned to them. `admin` users see all.

### Query parameters

| Param | Type | Notes |
|-------|------|-------|
| `search` | string | Match on name, phone, or area |
| `updated_after` | datetime | Incremental sync |
| `page` | integer | Default 1, 20 per page |

### Success response `200`

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "صيدلية الأمل",
        "phone": "0944100001",
        "address": "دمشق - المزة",
        "area": "المزة",
        "credit_limit": "500000.00",
        "opening_balance": "0.00",
        "rep": {
          "id": 2,
          "name": "أحمد الحسن"
        },
        "is_active": true,
        "updated_at": "2026-04-20T10:00:00+00:00",
        "deleted_at": null
      },
      {
        "id": 2,
        "name": "صيدلية الشفاء",
        "phone": "0944100002",
        "address": "دمشق - المالكي",
        "area": "المالكي",
        "credit_limit": "750000.00",
        "opening_balance": "50000.00",
        "rep": {
          "id": 2,
          "name": "أحمد الحسن"
        },
        "is_active": true,
        "updated_at": "2026-04-20T10:00:00+00:00",
        "deleted_at": null
      }
    ],
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 10
  },
  "message": "Pharmacies retrieved successfully."
}
```

---

## 6. Orders — Create

**`POST /api/v1/orders`** — 🔒 auth required

> **Rep restriction** — a `rep` user may only create orders for pharmacies **assigned to them**. Attempting to order for another pharmacy returns `403`.

### Request body

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `pharmacy_id` | integer | ✅ | Must exist |
| `items` | array | ✅ | At least 1 item |
| `items[].product_id` | integer | ✅ | Must exist |
| `items[].quantity` | integer | ✅ | ≥ 1 |
| `items[].unit_price` | decimal | ✅ | ≥ 0 |
| `items[].discount` | decimal | | Item-level discount, default 0 |
| `discount` | decimal | | Order-level discount applied after subtotal |
| `notes` | string | | Max 1000 characters |

```json
{
  "pharmacy_id": 1,
  "discount": 0,
  "notes": "طلبية دورية",
  "items": [
    {
      "product_id": 1,
      "quantity": 20,
      "unit_price": 250.00,
      "discount": 0
    },
    {
      "product_id": 6,
      "quantity": 10,
      "unit_price": 600.00,
      "discount": 500
    }
  ]
}
```

### Success response `200`

```json
{
  "success": true,
  "data": {
    "id": 12,
    "order_number": "ORD-2026-00012",
    "status": "pending",
    "subtotal": "10500.00",
    "discount": "0.00",
    "total": "10500.00",
    "notes": "طلبية دورية",
    "confirmed_at": null,
    "cancelled_at": null,
    "created_at": "2026-04-28T08:45:00+00:00",
    "pharmacy": {
      "id": 1,
      "name": "صيدلية الأمل",
      "phone": "0944100001",
      "address": "دمشق - المزة",
      "area": "المزة",
      "credit_limit": "500000.00",
      "opening_balance": "0.00",
      "is_active": true
    },
    "rep": {
      "id": 2,
      "name": "أحمد الحسن"
    },
    "items": [
      {
        "id": 23,
        "product_id": 1,
        "quantity": 20,
        "unit_price": "250.00",
        "discount": "0.00",
        "total": "5000.00",
        "product": {
          "id": 1,
          "name": "باراسيتامول 500 مج",
          "barcode": "6912345000001",
          "unit": "علبة",
          "form": "أقراص"
        }
      },
      {
        "id": 24,
        "product_id": 6,
        "quantity": 10,
        "unit_price": "600.00",
        "discount": "500.00",
        "total": "5500.00",
        "product": {
          "id": 6,
          "name": "أموكسيسيلين 500 مج",
          "barcode": "6912345000006",
          "unit": "علبة",
          "form": "كبسولات"
        }
      }
    ]
  },
  "message": "Order created successfully."
}
```

### Error — rep accessing unassigned pharmacy `403`

```json
{
  "success": false,
  "message": "Forbidden: you can only create orders for your assigned pharmacies."
}
```

### Error — validation `422`

```json
{
  "success": false,
  "message": "The items field is required.",
  "data": {
    "items": ["The items field is required."]
  }
}
```

---

## 7. Orders — List

**`GET /api/v1/orders`** — 🔒 auth required

> **Rep scoping** — `rep` users see only their own orders.

### Query parameters

| Param | Type | Notes |
|-------|------|-------|
| `status` | string | `pending` \| `confirmed` \| `cancelled` \| `draft` |
| `pharmacy_id` | integer | Filter by pharmacy |
| `from` | date `Y-m-d` | `created_at` ≥ |
| `to` | date `Y-m-d` | `created_at` ≤ |
| `page` | integer | Default 1, 15 per page |

### Success response `200`

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 12,
        "order_number": "ORD-2026-00012",
        "status": "pending",
        "subtotal": "10500.00",
        "discount": "0.00",
        "total": "10500.00",
        "notes": "طلبية دورية",
        "confirmed_at": null,
        "cancelled_at": null,
        "created_at": "2026-04-28T08:45:00+00:00",
        "pharmacy": {
          "id": 1,
          "name": "صيدلية الأمل",
          "phone": "0944100001"
        },
        "rep": {
          "id": 2,
          "name": "أحمد الحسن"
        }
      }
    ],
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 5
  },
  "message": "Orders retrieved successfully."
}
```

---

## 8. Orders — Show

**`GET /api/v1/orders/{id}`** — 🔒 auth required

> **Rep restriction** — `rep` users can only view orders they created (`rep_id = user.id`).

### Path parameter

| Param | Type | Notes |
|-------|------|-------|
| `id` | integer | Order ID |

### Success response `200`

Same as the single order object in [Create Order](#6-orders--create) (with `items` array included).

```json
{
  "success": true,
  "data": {
    "id": 12,
    "order_number": "ORD-2026-00012",
    "status": "pending",
    "subtotal": "10500.00",
    "discount": "0.00",
    "total": "10500.00",
    "notes": "طلبية دورية",
    "confirmed_at": null,
    "cancelled_at": null,
    "created_at": "2026-04-28T08:45:00+00:00",
    "pharmacy": { "id": 1, "name": "صيدلية الأمل", "phone": "0944100001" },
    "rep": { "id": 2, "name": "أحمد الحسن" },
    "items": [
      {
        "id": 23,
        "product_id": 1,
        "quantity": 20,
        "unit_price": "250.00",
        "discount": "0.00",
        "total": "5000.00",
        "product": {
          "id": 1,
          "name": "باراسيتامول 500 مج",
          "barcode": "6912345000001",
          "unit": "علبة",
          "form": "أقراص"
        }
      }
    ]
  },
  "message": "Order retrieved successfully."
}
```

### Error — not found / forbidden `403`

```json
{
  "success": false,
  "message": "Forbidden."
}
```

---

## 9. Orders — Confirm

Confirms a `pending` or `draft` order. This will:
1. Validate sufficient stock for every line item.
2. Create `TYPE_SALE` stock movements (deducts stock).
3. Post a `TYPE_DEBIT` account entry against the pharmacy.
4. Set `status` → `confirmed` and stamp `confirmed_at`.

**`POST /api/v1/orders/{id}/confirm`** — 🔒 auth required  
No request body required.

### Success response `200`

```json
{
  "success": true,
  "data": {
    "id": 12,
    "order_number": "ORD-2026-00012",
    "status": "confirmed",
    "subtotal": "10500.00",
    "discount": "0.00",
    "total": "10500.00",
    "confirmed_at": "2026-04-28T09:00:00+00:00",
    "cancelled_at": null,
    "created_at": "2026-04-28T08:45:00+00:00",
    "pharmacy": { "id": 1, "name": "صيدلية الأمل" },
    "items": [ { "id": 23, "product_id": 1, "quantity": 20, "unit_price": "250.00", "total": "5000.00" } ]
  },
  "message": "Order confirmed successfully."
}
```

### Error — insufficient stock `422`

```json
{
  "success": false,
  "message": "Insufficient stock for 'باراسيتامول 500 مج'.",
  "data": {
    "stock": ["Insufficient stock for 'باراسيتامول 500 مج'."]
  }
}
```

### Error — wrong status `422`

```json
{
  "success": false,
  "message": "Cannot confirm an order with status 'confirmed'.",
  "data": {
    "order": ["Cannot confirm an order with status 'confirmed'."]
  }
}
```

---

## 10. Orders — Cancel

Cancels any non-cancelled order. If the order was `confirmed`, this will:
1. Create `TYPE_SALE_CANCEL` stock movements (restores stock).
2. Post a `TYPE_CREDIT` account entry to reverse the debit.
3. Set `status` → `cancelled` and stamp `cancelled_at`.

For `draft`/`pending` orders: status changes only (no stock or accounting reversal needed).

**`POST /api/v1/orders/{id}/cancel`** — 🔒 auth required  
No request body required.

### Success response `200`

```json
{
  "success": true,
  "data": {
    "id": 12,
    "order_number": "ORD-2026-00012",
    "status": "cancelled",
    "subtotal": "10500.00",
    "discount": "0.00",
    "total": "10500.00",
    "confirmed_at": "2026-04-28T09:00:00+00:00",
    "cancelled_at": "2026-04-28T09:30:00+00:00",
    "created_at": "2026-04-28T08:45:00+00:00",
    "pharmacy": { "id": 1, "name": "صيدلية الأمل" },
    "items": [ { "id": 23, "product_id": 1, "quantity": 20, "unit_price": "250.00", "total": "5000.00" } ]
  },
  "message": "Order cancelled successfully."
}
```

### Error — already cancelled `422`

```json
{
  "success": false,
  "message": "This order is already cancelled.",
  "data": {
    "order": ["This order is already cancelled."]
  }
}
```

---

## 11. Payments — Create

Record a cash or bank payment from a pharmacy and automatically post a `TYPE_CREDIT` account entry.

**`POST /api/v1/payments`** — 🔒 auth required

> **Rep restriction** — `rep` users may only record payments for their assigned pharmacies.

### Request body

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `pharmacy_id` | integer | ✅ | Must exist |
| `amount` | decimal | ✅ | Must be > 0 |
| `method` | string | | `cash` (default) \| `bank` \| `other` |
| `order_id` | integer | | Optional — must belong to the same pharmacy |
| `notes` | string | | Max 1000 characters |
| `paid_at` | datetime | | ISO-8601, defaults to current server time |

```json
{
  "pharmacy_id": 1,
  "amount": 5000.00,
  "method": "cash",
  "order_id": 12,
  "notes": "دفعة جزئية أولى",
  "paid_at": "2026-04-28T10:00:00"
}
```

### Success response `200`

```json
{
  "success": true,
  "data": {
    "id": 7,
    "pharmacy_id": 1,
    "order_id": 12,
    "amount": "5000.00",
    "method": "cash",
    "notes": "دفعة جزئية أولى",
    "paid_at": "2026-04-28T10:00:00+00:00",
    "created_by": 2,
    "created_at": "2026-04-28T10:00:05+00:00",
    "pharmacy": {
      "id": 1,
      "name": "صيدلية الأمل",
      "phone": "0944100001",
      "area": "المزة"
    },
    "order": {
      "id": 12,
      "order_number": "ORD-2026-00012",
      "status": "confirmed",
      "total": "10500.00"
    }
  },
  "message": "Payment recorded successfully."
}
```

### Error — order belongs to different pharmacy `422`

```json
{
  "success": false,
  "message": "Validation failed.",
  "data": {
    "order_id": ["The selected order does not belong to this pharmacy."]
  }
}
```

### Error — rep accessing unassigned pharmacy `403`

```json
{
  "success": false,
  "message": "Forbidden. This pharmacy is not assigned to you."
}
```

---

## 12. Pharmacy Statement

Retrieve the full ledger statement for a pharmacy: all-time balance summary plus a date-filterable list of account entries.

**`GET /api/v1/pharmacies/{id}/statement`** — 🔒 auth required

> **Rep restriction** — `rep` users may only view statements for their assigned pharmacies.  
> **Balance formula:** `balance = opening_balance + total_debit − total_credit`  
> The balance totals are **always all-time** regardless of the date filter.

### Path parameter

| Param | Type | Notes |
|-------|------|-------|
| `id` | integer | Pharmacy ID |

### Query parameters

| Param | Type | Notes |
|-------|------|-------|
| `from` | date `Y-m-d` | Filter entries from this date (inclusive) |
| `to` | date `Y-m-d` | Filter entries up to this date (inclusive); must be ≥ `from` |

### Success response `200`

```json
{
  "success": true,
  "data": {
    "pharmacy": {
      "id": 1,
      "name": "صيدلية الأمل",
      "phone": "0944100001",
      "address": "دمشق - المزة",
      "area": "المزة",
      "credit_limit": "500000.00",
      "opening_balance": "0.00",
      "is_active": true
    },
    "opening_balance": 0.0,
    "total_debit": 10500.0,
    "total_credit": 5000.0,
    "balance": 5500.0,
    "entries": [
      {
        "id": 14,
        "pharmacy_id": 1,
        "order_id": 12,
        "payment_id": null,
        "type": "debit",
        "amount": "10500.00",
        "description": "Order confirmed: ORD-2026-00012",
        "entry_date": "2026-04-28",
        "created_by": 1,
        "created_at": "2026-04-28T09:00:05+00:00"
      },
      {
        "id": 15,
        "pharmacy_id": 1,
        "order_id": 12,
        "payment_id": 7,
        "type": "credit",
        "amount": "5000.00",
        "description": "Payment received via cash",
        "entry_date": "2026-04-28",
        "created_by": 2,
        "created_at": "2026-04-28T10:00:05+00:00"
      }
    ]
  },
  "message": "Statement retrieved successfully."
}
```

> `type` is either `"debit"` (pharmacy owes you) or `"credit"` (payment received / credit issued).

### Error — rep accessing unassigned pharmacy `403`

```json
{
  "success": false,
  "message": "Forbidden."
}
```

---

## 13. Product Stock

Get the current on-hand stock level for a single product. Calculated as the running sum of all `stock_movements.quantity` records for that product.

**`GET /api/v1/products/{id}/stock`** — 🔒 auth required

### Path parameter

| Param | Type | Notes |
|-------|------|-------|
| `id` | integer | Product ID |

### Success response `200`

```json
{
  "success": true,
  "data": {
    "product_id": 1,
    "product_name": "باراسيتامول 500 مج",
    "quantity": 480
  },
  "message": "Stock retrieved successfully."
}
```

> A `quantity` of `0` means out of stock. Negative values indicate a data discrepancy.

### Error — not found `404`

```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Product] 999"
}
```

---

## Flutter Integration Tips

### Dart model hints

```dart
// Parse ISO-8601 dates returned by the API
final confirmedAt = data['confirmed_at'] != null
    ? DateTime.parse(data['confirmed_at'])
    : null;

// Decimal amounts come as strings ("10500.00") — parse with:
final total = double.parse(data['total']);
```

### Offline-first sync pattern

```
App launch
  └─ Local DB empty?  → GET /api/v1/sync/bootstrap  → persist all sections
  └─ Already seeded?  → GET /api/v1/sync/changes?updated_after=<last_server_time>
                          → upsert changed records
                          → delete records where deleted_at != null
                          → save new server_time for next cycle
```

### Order number format

```
ORD-{YEAR}-{ID padded to 5 digits}
Example: ORD-2026-00012
```

### Status values

| Model | Field | Possible values |
|-------|-------|----------------|
| Order | `status` | `draft` · `pending` · `confirmed` · `cancelled` |
| Payment | `method` | `cash` · `bank` · `other` |
| AccountEntry | `type` | `debit` · `credit` |

### Authentication header for every protected request

```
Authorization: Bearer eyJ0eXAiOiJKV1Qi...
Content-Type: application/json
Accept: application/json
```

