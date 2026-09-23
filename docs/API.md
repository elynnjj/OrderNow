# OrderNow API Documentation

Comprehensive REST API reference for the OrderNow Restaurant Management System.

All requests should include the following HTTP headers:
```http
Accept: application/json
Content-Type: application/json
```

---

## Table of Contents
- [1. Menu Items API](#1-menu-items-api)
- [2. Ingredients API](#2-ingredients-api)
- [3. Orders API](#3-orders-api)
- [4. Dashboard API](#4-dashboard-api)
- [Postman Collection](#postman-collection)

---


## 1. Menu Items API

### 1.1 List Menu Items
- **Method**: `GET`
- **Path**: `/api/menu-items`
- **Description**: Returns all active and inactive non-deleted menu items along with their recipe ingredients.

#### Example Response (HTTP 200)
```json
{
  "data": [
    {
      "id": 1,
      "name": "Cheeseburger",
      "description": "Juicy beef patty with cheese, lettuce, and tomato",
      "price": 18,
      "is_active": true,
      "ingredients": [
        {
          "id": 1,
          "name": "Beef Patty",
          "unit": "pcs",
          "quantity": 1,
          "cost": null
        },
        {
          "id": 2,
          "name": "Burger Bun",
          "unit": "pcs",
          "quantity": 1,
          "cost": null
        }
      ],
      "created_at": "2026-09-23T12:42:50.000000Z",
      "updated_at": "2026-09-23T12:42:50.000000Z"
    }
  ]
}
```

---

### 1.2 Create Menu Item
- **Method**: `POST`
- **Path**: `/api/menu-items`
- **Description**: Creates a new menu item and syncs its recipe ingredients.

#### Example Request
```json
{
  "name": "Double Cheeseburger",
  "description": "Double beef patty with double cheese",
  "price": 26.50,
  "is_active": true,
  "ingredients": [
    { "ingredient_id": 1, "quantity": 2 },
    { "ingredient_id": 2, "quantity": 1 },
    { "ingredient_id": 3, "quantity": 2 }
  ]
}
```

#### Example Response (HTTP 201)
```json
{
  "data": {
    "id": 3,
    "name": "Double Cheeseburger",
    "description": "Double beef patty with double cheese",
    "price": 26.5,
    "is_active": true,
    "ingredients": [
      {
        "id": 1,
        "name": "Beef Patty",
        "unit": "pcs",
        "quantity": 2,
        "cost": null
      }
    ],
    "created_at": "2026-09-23T14:00:00.000000Z",
    "updated_at": "2026-09-23T14:00:00.000000Z"
  }
}
```

---

### 1.3 Get Single Menu Item
- **Method**: `GET`
- **Path**: `/api/menu-items/{id}`
- **Description**: Retrieves details for a specific menu item by ID.

---

### 1.4 Update Menu Item
- **Method**: `PUT` / `PATCH`
- **Path**: `/api/menu-items/{id}`
- **Description**: Updates fields of a menu item and optionally syncs recipe ingredients if provided.

#### Example Request
```json
{
  "price": 19.50,
  "is_active": true
}
```

---

### 1.5 Delete Menu Item
- **Method**: `DELETE`
- **Path**: `/api/menu-items/{id}`
- **Description**: Soft-deletes a menu item.
- **Response**: HTTP `204 No Content`.

---

## 2. Ingredients API

### 2.1 List Ingredients
- **Method**: `GET`
- **Path**: `/api/ingredients`
- **Description**: Returns all inventory ingredients and current stock levels.

#### Example Response (HTTP 200)
```json
{
  "data": [
    {
      "id": 1,
      "name": "Beef Patty",
      "unit": "pcs",
      "current_stock": 50,
      "reorder_level": 10,
      "is_low_stock": false,
      "created_at": "2026-09-23T12:42:50.000000Z",
      "updated_at": "2026-09-23T12:42:50.000000Z"
    }
  ]
}
```

---

### 2.2 Create Ingredient
- **Method**: `POST`
- **Path**: `/api/ingredients`
- **Description**: Registers a new ingredient item in inventory.

#### Example Request
```json
{
  "name": "Pickles",
  "unit": "slice",
  "current_stock": 100,
  "reorder_level": 20
}
```

---

### 2.3 Adjust Ingredient Stock
- **Method**: `PATCH`
- **Path**: `/api/ingredients/{id}/adjust`
- **Description**: Adjusts current stock level and records an audit log in `inventory_transactions`.

#### Example Request
```json
{
  "change": 50.000,
  "reason": "restock"
}
```

#### Example Response (HTTP 200)
```json
{
  "data": {
    "id": 1,
    "name": "Beef Patty",
    "unit": "pcs",
    "current_stock": 100,
    "reorder_level": 10,
    "is_low_stock": false,
    "created_at": "2026-09-23T12:42:50.000000Z",
    "updated_at": "2026-09-23T14:10:00.000000Z"
  }
}
```

---

### 2.4 Low Stock Alert List
- **Method**: `GET`
- **Path**: `/api/ingredients/low-stock`
- **Description**: Retrieves all ingredients where `current_stock <= reorder_level`.

---

## 3. Orders API

### 3.1 List Orders
- **Method**: `GET`
- **Path**: `/api/orders`
- **Description**: Paginated list of orders (15 per page) sorted by newest first.

#### Example Response (HTTP 200)
```json
{
  "data": [
    {
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
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/orders?page=1",
    "last": "http://127.0.0.1:8000/api/orders?page=2",
    "prev": null,
    "next": "http://127.0.0.1:8000/api/orders?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 2,
    "per_page": 15,
    "to": 15,
    "total": 24
  }
}
```


---

### 3.2 Create Order
- **Method**: `POST`
- **Path**: `/api/orders`
- **Description**: Creates a new pending order. Supports `idempotency_key` to avoid duplicate order placement.

#### Example Request
```json
{
  "items": [
    { "menu_item_id": 1, "quantity": 2 },
    { "menu_item_id": 2, "quantity": 1 }
  ],
  "idempotency_key": "ORDER-KEY-99481"
}
```

#### Example Response (HTTP 201)
```json
{
  "data": {
    "id": 1,
    "order_number": "#4821",
    "status": "pending",
    "total": 44,
    "items": [
      {
        "id": 1,
        "menu_item_id": 1,
        "menu_item_name": "Cheeseburger",
        "quantity": 2,
        "unit_price": 18,
        "subtotal": 36
      },
      {
        "id": 2,
        "menu_item_id": 2,
        "menu_item_name": "Fries",
        "quantity": 1,
        "unit_price": 8,
        "subtotal": 8
      }
    ],
    "created_at": "2026-09-23T14:15:00.000000Z",
    "updated_at": "2026-09-23T14:15:00.000000Z"
  }
}
```

---

### 3.3 Get Single Order
- **Method**: `GET`
- **Path**: `/api/orders/{id}`
- **Description**: Retrieves order details by ID.

---

### 3.4 Confirm Order (Concurrency-Safe)
- **Method**: `POST`
- **Path**: `/api/orders/{id}/confirm`
- **Description**: Confirms order, locks ingredient rows, validates stock sufficiency, and deducts inventory atomically.

#### Worked Example: Success (HTTP 200)
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

#### Worked Example: Insufficient Stock Failure (HTTP 422)
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

### 3.5 Cancel Order
- **Method**: `POST`
- **Path**: `/api/orders/{id}/cancel`
- **Description**: Cancels an unconfirmed order. Confirmed orders cannot be cancelled.

---

## 4. Dashboard API

### 4.1 Executive Dashboard Metrics
- **Method**: `GET`
- **Path**: `/api/dashboard`
- **Description**: Returns today's sales metrics, low stock alert items, recent orders feed, and top-selling menu items.

#### Example Response (HTTP 200)
```json
{
  "today": {
    "sales": 184.50,
    "order_count": 8,
    "confirmed_count": 6,
    "pending_count": 2
  },
  "low_stock_ingredients": [
    {
      "id": 6,
      "name": "Fries",
      "unit": "g",
      "current_stock": 300,
      "reorder_level": 1000
    }
  ],
  "recent_orders": [
    {
      "id": 1,
      "order_number": "#4821",
      "status": "confirmed",
      "total": 44,
      "item_count": 3,
      "created_at": "2026-09-23T14:15:00.000Z"
    }
  ],
  "top_selling_today": [
    {
      "id": 1,
      "name": "Cheeseburger",
      "total_qty": "12"
    },
    {
      "id": 2,
      "name": "Fries",
      "total_qty": "8"
    }
  ]
}
```

---

## Postman Collection

To test the OrderNow API endpoints in Postman:

1. Import your Postman collection or configure a new environment with `base_url` set to `http://127.0.0.1:8000/api`.
2. Configure mandatory request headers for all requests:
   - `Accept: application/json`
   - `Content-Type: application/json` (for `POST`, `PUT`, `PATCH` requests)
3. Refer to the [Testing in Postman section in README.md](../README.md#testing-in-postman) for the recommended request sequence.

