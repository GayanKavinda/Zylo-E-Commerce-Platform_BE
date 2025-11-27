# E-Commerce Platform API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

---

## 📦 1. Shopping Cart & Checkout

### Cart Management

#### Get Cart
```http
GET /cart
Authorization: Bearer {token}
```
**Response:**
```json
{
  "cart_items": [
    {
      "id": 1,
      "product": {...},
      "quantity": 2,
      "subtotal": 299.98
    }
  ],
  "total": 299.98,
  "items_count": 1
}
```

#### Add to Cart
```http
POST /cart
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 2
}
```

#### Update Cart Item
```http
PUT /cart/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "quantity": 3
}
```

#### Remove from Cart
```http
DELETE /cart/{id}
Authorization: Bearer {token}
```

#### Clear Cart
```http
DELETE /cart
Authorization: Bearer {token}
```

---

## 🛍️ 2. Product Catalog

### Public Endpoints

#### Get Products (with filters)
```http
GET /products?search={query}&category={category}&min_price={price}&max_price={price}&sort_by={field}&sort_order={asc|desc}&per_page={number}
```
**Query Parameters:**
- `search` - Search in name and description
- `category` - Filter by category
- `min_price` - Minimum price
- `max_price` - Maximum price
- `min_rating` - Minimum average rating
- `in_stock_only` - Only show in-stock products (boolean)
- `sort_by` - Sort field (price, name, popularity, rating, created_at)
- `sort_order` - asc or desc
- `per_page` - Items per page (default: 12)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Wireless Bluetooth Headphones",
      "price": 149.99,
      "discount_price": 119.99,
      "stock": 50,
      "category": "Electronics",
      "images": ["url1", "url2"],
      "average_rating": 4.5,
      "reviews_count": 23
    }
  ],
  "current_page": 1,
  "total": 100
}
```

#### Get Single Product
```http
GET /products/{id}
```

#### Get Featured Products
```http
GET /products/featured
```

#### Get Categories
```http
GET /products/categories
```

#### Get Related Products
```http
GET /products/{id}/related
```

---

## 📦 3. Order Management System

### Customer Orders

#### Create Order
```http
POST /orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipping_address": "123 Main St, City, State 12345",
  "billing_address": "123 Main St, City, State 12345",
  "payment_method": "credit_card",
  "notes": "Please deliver before 5 PM"
}
```

**Response:**
```json
{
  "message": "Order created successfully",
  "order": {
    "id": 1,
    "order_number": "ORD-ABC123",
    "total_amount": 329.98,
    "subtotal": 299.98,
    "tax": 30.00,
    "shipping_fee": 10.00,
    "status": "pending",
    "payment_status": "pending",
    "items": [...]
  }
}
```

#### Get User Orders
```http
GET /orders
Authorization: Bearer {token}
```

#### Get Single Order
```http
GET /orders/{id}
Authorization: Bearer {token}
```

#### Cancel Order
```http
POST /orders/{id}/cancel
Authorization: Bearer {token}
```

### Admin Order Management

#### Get All Orders (Admin)
```http
GET /admin/orders?status={status}&payment_status={status}&search={query}
Authorization: Bearer {token}
Role: admin, superadmin
```

#### Update Order Status (Admin)
```http
PUT /admin/orders/{id}/status
Authorization: Bearer {token}
Role: admin, superadmin
Content-Type: application/json

{
  "status": "shipped"
}
```
**Status Options:** pending, processing, shipped, delivered, cancelled

#### Update Payment Status (Admin)
```http
PUT /admin/orders/{id}/payment-status
Authorization: Bearer {token}
Role: admin, superadmin
Content-Type: application/json

{
  "payment_status": "paid",
  "transaction_id": "TXN123456"
}
```
**Payment Status Options:** pending, paid, failed, refunded

#### Get Order Statistics (Admin)
```http
GET /admin/orders/statistics
Authorization: Bearer {token}
Role: admin, superadmin
```

**Response:**
```json
{
  "total_orders": 150,
  "pending_orders": 20,
  "processing_orders": 30,
  "shipped_orders": 40,
  "delivered_orders": 50,
  "cancelled_orders": 10,
  "total_revenue": 45000.00,
  "pending_payments": 5000.00,
  "recent_orders": [...]
}
```

---

## 👤 4. Seller Dashboard

### Dashboard Overview

#### Get Seller Dashboard
```http
GET /seller/dashboard
Authorization: Bearer {token}
Role: seller
```

**Response:**
```json
{
  "products": {
    "total": 25,
    "active": 23,
    "out_of_stock": 2
  },
  "sales": {
    "total_revenue": 12500.00,
    "total_orders": 85,
    "pending_orders": 5,
    "processing_orders": 10,
    "shipped_orders": 70
  },
  "recent_sales": [...],
  "top_products": [...]
}
```

### Product Management

#### Get Seller Products
```http
GET /seller/products?is_active={boolean}&search={query}
Authorization: Bearer {token}
Role: seller
```

#### Create Product
```http
POST /seller/products
Authorization: Bearer {token}
Role: seller
Content-Type: application/json

{
  "name": "Product Name",
  "price": 99.99,
  "discount_price": 79.99,
  "stock": 100,
  "description": "Product description",
  "category": "Electronics",
  "images": ["url1", "url2"],
  "sku": "PROD-001",
  "is_active": true
}
```

#### Update Product
```http
PUT /seller/products/{id}
Authorization: Bearer {token}
Role: seller
Content-Type: application/json
```

#### Delete Product
```http
DELETE /seller/products/{id}
Authorization: Bearer {token}
Role: seller
```

### Order Fulfillment

#### Get Orders to Fulfill
```http
GET /seller/orders?fulfillment_status={status}&order_status={status}
Authorization: Bearer {token}
Role: seller
```

**Fulfillment Status Options:** pending, processing, shipped, delivered

#### Update Fulfillment Status
```http
PUT /seller/orders/{id}/fulfillment-status
Authorization: Bearer {token}
Role: seller
Content-Type: application/json

{
  "fulfillment_status": "shipped"
}
```

### Analytics

#### Get Seller Analytics
```http
GET /seller/analytics?period={days}
Authorization: Bearer {token}
Role: seller
```

**Response:**
```json
{
  "revenue_timeline": [...],
  "product_performance": [...],
  "category_breakdown": [...]
}
```

#### Get Inventory Alerts
```http
GET /seller/inventory-alerts?threshold={number}
Authorization: Bearer {token}
Role: seller
```

**Response:**
```json
{
  "low_stock": [...],
  "out_of_stock": [...]
}
```

---

## ⭐ 5. Reviews & Ratings

### Get Product Reviews
```http
GET /products/{productId}/reviews
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user": {
        "id": 1,
        "name": "John Doe"
      },
      "rating": 5,
      "comment": "Great product!",
      "is_verified_purchase": true,
      "created_at": "2025-01-15T10:30:00Z"
    }
  ]
}
```

### Create Review
```http
POST /reviews
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1,
  "rating": 5,
  "comment": "Excellent product, highly recommend!"
}
```

### Update Review
```http
PUT /reviews/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "rating": 4,
  "comment": "Updated review"
}
```

### Delete Review
```http
DELETE /reviews/{id}
Authorization: Bearer {token}
```

### Get User's Reviews
```http
GET /my-reviews
Authorization: Bearer {token}
```

---

## 🔐 Authentication

### Register
```http
POST /register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "customer"
}
```

**Roles:** customer, seller, admin, superadmin

### Login
```http
POST /login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "user": {...},
  "token": "1|abcdef123456..."
}
```

### Logout
```http
POST /logout
Authorization: Bearer {token}
```

### Get Current User
```http
GET /user
Authorization: Bearer {token}
```

---

## 👥 Admin - User Management

### Get All Users
```http
GET /admin/users
Authorization: Bearer {token}
Role: admin, superadmin
```

### Create User
```http
POST /admin/users
Authorization: Bearer {token}
Role: admin, superadmin
Content-Type: application/json

{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "role": "seller"
}
```

### Update User
```http
PUT /admin/users/{id}
Authorization: Bearer {token}
Role: admin, superadmin
```

### Delete User
```http
DELETE /admin/users/{id}
Authorization: Bearer {token}
Role: admin, superadmin
```

### Change User Role
```http
POST /admin/users/{id}/change-role
Authorization: Bearer {token}
Role: admin, superadmin
Content-Type: application/json

{
  "role": "seller"
}
```

---

## 📊 Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## 🔑 Default Credentials

### Super Admin
- Email: `superadmin@example.com`
- Password: `password`

### Admin
- Email: `admin@example.com`
- Password: `password`

### Seller
- Email: `seller@example.com`
- Password: `password`

### Customer
- Email: `customer@example.com`
- Password: `password`

---

## 🎯 Feature Summary

### ✅ Feature 1: Shopping Cart & Checkout
- ✅ Product catalog with advanced filters (search, category, price range, rating)
- ✅ Product detail pages with reviews and related products
- ✅ Shopping cart functionality (add, update, remove, clear)
- ✅ Checkout process with address and payment method
- ✅ Order confirmation with order number

### ✅ Feature 2: Seller Dashboard
- ✅ Seller registration and authentication
- ✅ Product management (CRUD operations)
- ✅ Order fulfillment tracking
- ✅ Sales analytics and revenue charts
- ✅ Inventory tracking and low stock alerts

### ✅ Feature 3: Order Management System
- ✅ Order creation from cart
- ✅ Order tracking with status updates
- ✅ Multiple order statuses (pending, processing, shipped, delivered, cancelled)
- ✅ Payment status tracking
- ✅ Order history for customers
- ✅ Admin order management dashboard
- ✅ Order statistics and analytics

### ✅ Feature 4: Advanced Features
- ✅ Analytics dashboard with charts data
- ✅ Product image support (JSON array)
- ✅ Customer reviews and ratings system
- ✅ Advanced search and filters
- ✅ Product categories
- ✅ Discount pricing
- ✅ Stock management
- ✅ SKU system
- ✅ Product views tracking
- ✅ Verified purchase reviews
