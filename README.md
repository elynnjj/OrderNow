# OrderNow

OrderNow is a concurrency-safe restaurant management REST API built with Laravel 12 and MySQL. It provides comprehensive backend services to manage menu items, inventory ingredients, recipe mappings, multi-stage order lifecycles, and real-time executive dashboard analytics.

The system features atomic inventory deduction with pessimistic row-level database locking (`SELECT ... FOR UPDATE`), idempotency controls, audit ledger logging, and database-level check constraints to guarantee stock integrity under concurrent request scenarios.

---

## Features

- **Menu & Recipe Management**: Dynamic menu item CRUD operations with recipe-to-ingredient mappings stored in a relational pivot table (`recipe_ingredients`).
- **Inventory Control**: Real-time ingredient stock tracking with stock adjustments, transaction audit logging (`inventory_transactions`), and low-stock threshold alerts.
- **Order Lifecycle Management**: Multi-step order workflow (`pending` $\rightarrow$ `confirmed` / `cancelled`) with automatic unit price snapshotting and subtotal calculations.
- **Concurrency-Safe Stock Deduction**: Atomic inventory deduction upon order confirmation using DB transactions, deterministic lock ordering, and row locks (`lockForUpdate()`).
- **Custom Error Handling**: Custom `InsufficientStockException` providing structured HTTP 422 JSON payloads detailing missing quantities and ingredients.
- **Idempotent Order Operations**: Built-in idempotency key support to prevent duplicate order placement during network retries.
- **Executive Dashboard API**: Real-time sales metrics, order status breakdown, low-stock alerts, recent order feed, and top-selling menu items.

---

## Technology Stack

| Technology | Version | Purpose |
| :--- | :--- | :--- |
| **PHP** | 8.2.12 | Primary Runtime Language |
| **Laravel** | 12.69.2 | Web Application Framework |
| **MySQL** | 8.0+ / MariaDB 10.4+ | Relational Database Engine (via XAMPP) |
| **Composer** | 2.8.4 | Dependency Manager |
| **Laravel Sanctum** | Installed | API Authentication Infrastructure |
| **Postman** | Latest | API Manual Testing & Endpoint Verification |

---

## Architecture

OrderNow follows the standard Model-View-Controller (MVC) pattern augmented with a dedicated **Service Layer**:

- **Controllers** (`app/Http/Controllers/Api/`): Process HTTP requests, validate authorization, and format API Resource responses.
- **Form Requests** (`app/Http/Requests/`): Encapsulate request validation rules and custom error messages.
- **Service Layer** (`app/Services/OrderService.php`): Encapsulates core business logic for order creation, transactional stock deduction, idempotency verification, and cancellation.
- **Exceptions** (`app/Exceptions/`): Custom domain exceptions like `InsufficientStockException` with built-in HTTP JSON rendering.
- **API Resources** (`app/Http/Resources/`): Transform Eloquent models into consistent API JSON payloads.

```
Client (Postman/Frontend) ──> Routes (routes/api.php)
                                   │
                                   ▼
                         Controllers & Requests
                                   │
                                   ▼
                        Service Layer (OrderService)
                         ┌─────────┴─────────┐
                         ▼                   ▼
                  DB Transactions       Row Locks (lockForUpdate)
                         │                   │
                         └─────────┬─────────┘
                                   ▼
                             MySQL Database
```

---

## Database Schema

The system relies on 6 domain-specific database tables:

| Table | Description | Key Columns |
| :--- | :--- | :--- |
| `ingredients` | Inventory stock master table | `id`, `name`, `unit`, `current_stock`, `reorder_level` |
| `menu_items` | Active restaurant menu items (Soft Deletes) | `id`, `name`, `description`, `price`, `is_active`, `deleted_at` |
| `recipe_ingredients` | Recipe mapping pivot table | `id`, `menu_item_id`, `ingredient_id`, `quantity` |
| `orders` | Customer order headers | `id`, `order_number`, `status`, `total`, `idempotency_key` |
| `order_items` | Order line item details | `id`, `order_id`, `menu_item_id`, `quantity`, `unit_price`, `subtotal` |
| `inventory_transactions` | Inventory audit ledger | `id`, `ingredient_id`, `change`, `reason`, `reference_id`, `stock_after` |

> [!IMPORTANT]
> Database-level CHECK constraint applied to `ingredients`: `ALTER TABLE ingredients ADD CONSTRAINT chk_ingredients_stock_non_negative CHECK (current_stock >= 0)`.

---

## Seeded Demo Data

### Ingredients
- **Beef Patty**: 50 pcs (Reorder: 10)
- **Burger Bun**: 40 pcs (Reorder: 10)
- **Cheese**: 60 slices (Reorder: 15)
- **Lettuce**: 2000 g (Reorder: 500)
- **Tomato**: 1500 g (Reorder: 400)
- **Fries**: 300 g (Reorder: 1000 — *deliberately low stock to trigger alert*)

### Menu Items & Recipes
1. **Cheeseburger** (RM 18.00): 1 Beef Patty, 1 Burger Bun, 1 Cheese slice, 20g Lettuce, 15g Tomato.
2. **Fries** (RM 8.00): 150g Fries.

---

## Requirements

- PHP 8.2+
- Composer 2.x
- MySQL 8.0+ / MariaDB 10.4+ (e.g. via XAMPP)
- Postman (optional, for testing)

---

## Setup Instructions

1. **Clone Repository**:
   ```bash
   git clone <repository-url>
   cd OrderNow
   ```

2. **Install Composer Dependencies**:
   ```bash
   composer install
   ```

3. **Configure Environment File**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup MySQL Database**:
   Create a MySQL database named `ordernow_db` in XAMPP / phpMyAdmin / MySQL CLI.
   Configure `.env` credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ordernow_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

6. **Start Application Server**:
   ```bash
   php artisan serve
   ```
   The API will be live at `http://127.0.0.1:8000/api`.

---

## API Endpoints

Full API endpoint specifications, parameters, and worked request/response examples are available in [docs/API.md](docs/API.md).

### Quick Reference

| Group | Method | Endpoint | Description |
| :--- | :--- | :--- | :--- |
| **Menu Items** | `GET` | `/api/menu-items` | List menu items with recipe ingredients |
| | `POST` | `/api/menu-items` | Create menu item & sync recipe ingredients |
| | `GET` | `/api/menu-items/{id}` | Get single menu item details |
| | `PUT` | `/api/menu-items/{id}` | Update menu item details or recipes |
| | `DELETE` | `/api/menu-items/{id}` | Soft delete menu item |
| **Ingredients**| `GET` | `/api/ingredients` | List all inventory ingredients |
| | `POST` | `/api/ingredients` | Register new ingredient |
| | `PATCH`| `/api/ingredients/{id}/adjust` | Adjust stock & create audit transaction |
| | `GET` | `/api/ingredients/low-stock` | List ingredients at/below reorder level |
| **Orders** | `GET` | `/api/orders` | Paginated orders list (15 per page) |
| | `POST` | `/api/orders` | Create pending order with idempotency key |
| | `GET` | `/api/orders/{id}` | Get single order details |
| | `POST` | `/api/orders/{id}/confirm` | Confirm order & deduct inventory |
| | `POST` | `/api/orders/{id}/cancel` | Cancel unconfirmed order |
| **Dashboard** | `GET` | `/api/dashboard` | Executive sales metrics & top items |

---

## Worked Example: Order Confirmation

### Success Confirmation Response (`POST /api/orders/1/confirm`)
```json
{
  "data": {
    "id": 1,
    "order_number": "#4821",
    "status": "confirmed",
    "total": 44,
    "items": [
      {
        "id": 1,
        "menu_item_id": 1,
        "menu_item_name": "Cheeseburger",
        "quantity": 2,
        "unit_price": 18,
        "subtotal": 36
      }
    ],
    "created_at": "2026-09-23T14:15:00.000000Z",
    "updated_at": "2026-09-23T14:16:00.000000Z"
  }
}
```

### Insufficient Stock Response (`POST /api/orders/2/confirm` — HTTP 422)
```json
{
  "error": "INSUFFICIENT_STOCK",
  "message": "Not enough Fries.",
  "details": {
    "ingredient_id": 6,
    "ingredient_name": "Fries",
    "required": 450,
    "available": 300,
    "unit": "g"
  }
}
```

---

## Concurrency Handling

OrderNow implements pessimistic concurrency control in `app/Services/OrderService.php` to handle concurrent order confirmations safely:

1. **Transaction Isolation**: Order confirmation executes entirely within `DB::transaction()`.
2. **Deterministic Lock Ordering**: Ingredient IDs required across all order items are extracted and sorted ascending (`orderBy('id')`) before acquiring pessimistic row locks (`lockForUpdate()`). Sorting guarantees consistent lock acquisition order across threads, preventing database deadlocks.
3. **Stock Verification**: Stock levels are evaluated against total required quantities inside the locked block.
4. **Atomic Deduction & Ledger Recording**: Current stock is decremented and an `inventory_transactions` row is inserted before transaction commit.
5. **Database Constraint**: `chk_ingredients_stock_non_negative CHECK (current_stock >= 0)` serves as storage-engine level defense-in-depth.

> **Scenario**: With **5 Beef Patties** in stock, two simultaneous requests each order 3 Cheeseburgers. Because `confirmOrder()` locks ingredient rows in deterministic order, Request B waits for Request A to commit. Request B then reads updated stock (`2`), fails validation (`2 < 3`), rolls back, and returns `422 INSUFFICIENT_STOCK`. Inventory never goes negative.

---

## Error Responses

| Status Code | Error Code | Description |
| :--- | :--- | :--- |
| `404 Not Found` | Standard | Resource or ID does not exist |
| `422 Unprocessable` | Validation Error | Request body failed validation rules |
| `422 Unprocessable` | `INSUFFICIENT_STOCK` | Inventory stock insufficient during confirmation |
| `500 Server Error` | System Exception | Unhandled error |

---

## Testing in Postman

Request headers:
```http
Accept: application/json
Content-Type: application/json
```

### Suggested Test Sequence
1. **Check Initial Stock**: `GET http://127.0.0.1:8000/api/ingredients`
2. **Create Order**: `POST http://127.0.0.1:8000/api/orders`
   ```json
   {
     "items": [
       { "menu_item_id": 1, "quantity": 2 },
       { "menu_item_id": 2, "quantity": 1 }
     ],
     "idempotency_key": "test-order-001"
   }
   ```
3. **Confirm Order**: `POST http://127.0.0.1:8000/api/orders/1/confirm`
4. **Verify Stock Deduction**: `GET http://127.0.0.1:8000/api/ingredients`
5. **Test Excess Quantity Rejection**:
   - Create order with 1000 Cheeseburgers (`POST /api/orders`)
   - Confirm order (`POST /api/orders/{id}/confirm`) $\rightarrow$ Returns HTTP `422` with `INSUFFICIENT_STOCK`.
6. **Check Executive Dashboard**: `GET http://127.0.0.1:8000/api/dashboard`

## Postman Collection

The file [`docs/OrderNow.postman_collection.json`](docs/OrderNow.postman_collection.json) 
contains all 15 API endpoints pre-configured for testing.

**To use:**
1. Open Postman → **File → Import**
2. Select the JSON file
3. (Optional) Edit the `base_url` collection variable if your server runs on a different host/port
4. Run requests in the recommended sequence (see [Testing in Postman](#testing-in-postman))

**Included endpoints:** Menu Items (5), Ingredients (4), Orders (5), Dashboard (1)

---

## Project Structure

```
OrderNow/
├── app/
│   ├── Exceptions/
│   │   └── InsufficientStockException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── DashboardController.php
│   │   │       ├── IngredientController.php
│   │   │       ├── MenuItemController.php
│   │   │       └── OrderController.php
│   │   ├── Requests/
│   │   │   ├── AdjustStockRequest.php
│   │   │   ├── StoreIngredientRequest.php
│   │   │   ├── StoreMenuItemRequest.php
│   │   │   ├── StoreOrderRequest.php
│   │   │   └── UpdateMenuItemRequest.php
│   │   └── Resources/
│   │       ├── IngredientResource.php
│   │       ├── MenuItemResource.php
│   │       └── OrderResource.php
│   ├── Models/
│   │   ├── Ingredient.php
│   │   ├── InventoryTransaction.php
│   │   ├── MenuItem.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   └── RecipeIngredient.php
│   └── Services/
│       └── OrderService.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── IngredientSeeder.php
│       ├── MenuItemSeeder.php
│       └── RecipeSeeder.php
├── docs/
│   ├── API.md
│   └── OrderNow.postman_collection.json
│
├── routes/
│   └── api.php
└── README.md
```

---

## If I Had Another Day

If given more time, the following improvements would meaningfully 
strengthen the system:

### 1. A More Complete Ordering Flow
The current UI is functional but minimal — it prioritises proving the 
backend logic over polish. I would build a proper customer-facing 
ordering experience: a mobile-friendly menu browser, cart persistence, 
table or takeaway selection, and a smoother checkout flow with 
quantity adjustments and order notes.

### 2. Role-Based Access Control
Currently the system has no authentication — anyone with the URL can 
access any endpoint. I would add Sanctum-based authentication with at 
least three roles:
- **Administrator** — full access to menu, inventory, pricing, and reports
- **Cashier** — can create orders and view their own sales
- **Kitchen Staff** — can view pending orders on a dedicated Kitchen 
  Display Screen (KDS) and mark them as *preparing*, *ready*, or *served*

This would require extending the `orders.status` enum to model the 
kitchen workflow, and adding authorization policies to each API route.

### 3. Payment Handling
The system currently stops at order confirmation — no payment is 
recorded. I would add a `payments` table and a checkout step supporting 
at least:
- Cash / card / e-wallet as payment methods
- Partial payments and refunds
- Automatic receipt generation (PDF or print-friendly view)

This would also mean extending the order lifecycle beyond `confirmed` to 
include `paid` and `completed`.

### 4. Automated Testing
The current system was validated manually through Postman (see 
[docs/API.md](docs/API.md) and the included Postman collection). While 
the concurrency behaviour was explicitly demonstrated, automated tests 
would provide stronger regression safety. I would add PHPUnit feature 
tests covering:
- Successful order deducts inventory
- Insufficient stock rejects the order
- Partial failure rolls back the transaction
- Concurrent confirms — only one succeeds
- Idempotent confirmation

### 5. Incomplete Areas (Honest Disclosure)
- The **frontend not to its fullest best form** — but it should demonstrates the API 
  works end-to-end but does not include search, filtering beyond basic 
  status, or mobile-optimised layouts.
- **No authentication or rate limiting** is implemented; the API is 
  fully open for local testing.
- **Automated tests have not yet been written** — testing is currently 
  manual via Postman.


---

## License

The OrderNow project is open-sourced software licensed under the [MIT license](LICENSE).
