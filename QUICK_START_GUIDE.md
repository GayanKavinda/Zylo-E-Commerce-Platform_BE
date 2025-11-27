# 🚀 Quick Start Guide - E-Commerce Platform

## 📊 Current Database State

- ✅ **5 Users** (superadmin, admin, seller, customer)
- ✅ **12 Products** across 7 categories
- ✅ **4 Roles** with permissions
- ✅ **51 API Endpoints** ready to use

---

## 🎯 Getting Started in 5 Minutes

### Step 1: Start the Server
```bash
cd backend
php artisan serve
```
Server will run at: `http://localhost:8000`

### Step 2: Test with cURL or Postman

#### Example 1: Login as Customer
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "customer@example.com",
    "password": "password"
  }'
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "Customer User",
    "email": "customer@example.com",
    "role": "customer"
  },
  "token": "1|abcdef123456..."
}
```

#### Example 2: Browse Products
```bash
curl http://localhost:8000/api/products
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Wireless Bluetooth Headphones",
      "price": "149.99",
      "discount_price": "119.99",
      "stock": 50,
      "category": "Electronics",
      "average_rating": 0,
      "reviews_count": 0
    }
  ],
  "current_page": 1,
  "total": 12
}
```

#### Example 3: Add to Cart (Authenticated)
```bash
curl -X POST http://localhost:8000/api/cart \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

#### Example 4: Create Order
```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "123 Main St, City, State 12345",
    "payment_method": "credit_card"
  }'
```

---

## 🎨 Use Case Examples

### Use Case 1: Customer Shopping Flow

1. **Browse Products**
   ```
   GET /api/products?category=Electronics&sort_by=price&sort_order=asc
   ```

2. **View Product Details**
   ```
   GET /api/products/1
   ```

3. **Add to Cart**
   ```
   POST /api/cart
   Body: { "product_id": 1, "quantity": 2 }
   ```

4. **View Cart**
   ```
   GET /api/cart
   ```

5. **Checkout**
   ```
   POST /api/orders
   Body: { 
     "shipping_address": "...",
     "payment_method": "credit_card"
   }
   ```

6. **View Orders**
   ```
   GET /api/orders
   ```

7. **Leave a Review**
   ```
   POST /api/reviews
   Body: {
     "product_id": 1,
     "rating": 5,
     "comment": "Great product!"
   }
   ```

---

### Use Case 2: Seller Management Flow

1. **Login as Seller**
   ```
   POST /api/login
   Body: {
     "email": "seller@example.com",
     "password": "password"
   }
   ```

2. **View Dashboard**
   ```
   GET /api/seller/dashboard
   ```
   Returns: Products count, sales revenue, order statistics

3. **Create New Product**
   ```
   POST /api/seller/products
   Body: {
     "name": "New Product",
     "price": 99.99,
     "stock": 100,
     "category": "Electronics",
     "description": "..."
   }
   ```

4. **View Orders to Fulfill**
   ```
   GET /api/seller/orders?fulfillment_status=pending
   ```

5. **Update Fulfillment Status**
   ```
   PUT /api/seller/orders/1/fulfillment-status
   Body: { "fulfillment_status": "shipped" }
   ```

6. **Check Inventory Alerts**
   ```
   GET /api/seller/inventory-alerts?threshold=10
   ```

7. **View Analytics**
   ```
   GET /api/seller/analytics?period=30
   ```

---

### Use Case 3: Admin Management Flow

1. **Login as Admin**
   ```
   POST /api/login
   Body: {
     "email": "admin@example.com",
     "password": "password"
   }
   ```

2. **View All Orders**
   ```
   GET /api/admin/orders?status=pending
   ```

3. **View Statistics**
   ```
   GET /api/admin/orders/statistics
   ```
   Returns: Total orders, revenue, orders by status

4. **Update Order Status**
   ```
   PUT /api/admin/orders/1/status
   Body: { "status": "shipped" }
   ```

5. **Update Payment Status**
   ```
   PUT /api/admin/orders/1/payment-status
   Body: {
     "payment_status": "paid",
     "transaction_id": "TXN123"
   }
   ```

6. **Manage Users**
   ```
   GET /api/admin/users
   POST /api/admin/users
   PUT /api/admin/users/1
   DELETE /api/admin/users/1
   ```

---

## 🧪 Testing with Postman

### Import Collection Steps:
1. Create a new collection in Postman
2. Add environment variables:
   - `base_url`: `http://localhost:8000/api`
   - `token`: (will be set after login)

### Sample Requests:

**Collection Structure:**
```
E-Commerce API
├── Auth
│   ├── Register
│   ├── Login (saves token)
│   └── Logout
├── Products
│   ├── Get All Products
│   ├── Get Product Details
│   ├── Get Categories
│   └── Get Featured Products
├── Cart
│   ├── Get Cart
│   ├── Add to Cart
│   ├── Update Cart Item
│   └── Remove from Cart
├── Orders
│   ├── Create Order
│   ├── Get My Orders
│   └── Get Order Details
├── Reviews
│   ├── Get Product Reviews
│   ├── Create Review
│   └── Get My Reviews
├── Seller
│   ├── Dashboard
│   ├── Get Products
│   ├── Create Product
│   ├── Get Orders
│   └── Update Fulfillment
└── Admin
    ├── Get All Orders
    ├── Order Statistics
    └── Manage Users
```

---

## 📝 Sample Product Categories

Your database includes products in these categories:
- **Electronics** (3 products)
- **Clothing** (2 products)
- **Home & Garden** (2 products)
- **Books** (1 product)
- **Sports & Outdoors** (2 products)
- **Beauty & Personal Care** (1 product)
- **Toys & Games** (1 product)

---

## 🔍 Advanced Search Examples

### Filter by Category
```
GET /api/products?category=Electronics
```

### Filter by Price Range
```
GET /api/products?min_price=50&max_price=200
```

### Search by Name
```
GET /api/products?search=headphones
```

### Sort by Price (Ascending)
```
GET /api/products?sort_by=price&sort_order=asc
```

### Sort by Rating
```
GET /api/products?sort_by=rating&sort_order=desc
```

### In Stock Only
```
GET /api/products?in_stock_only=true
```

### Combined Filters
```
GET /api/products?category=Electronics&min_price=100&max_price=300&sort_by=price&in_stock_only=true
```

---

## 🔐 Authentication Flow

### For Web Applications:
1. User logs in → Receive token
2. Store token in localStorage/sessionStorage
3. Include token in all authenticated requests:
   ```javascript
   headers: {
     'Authorization': `Bearer ${token}`,
     'Content-Type': 'application/json'
   }
   ```

### For Mobile Applications:
1. User logs in → Receive token
2. Store token securely (Keychain/KeyStore)
3. Include token in API requests

---

## 🎯 Role-Based Access

| Endpoint | Customer | Seller | Admin | Public |
|----------|----------|--------|-------|--------|
| View Products | ✅ | ✅ | ✅ | ✅ |
| Cart | ✅ | ✅ | ✅ | ❌ |
| Create Order | ✅ | ✅ | ✅ | ❌ |
| View Own Orders | ✅ | ✅ | ✅ | ❌ |
| Create Product | ❌ | ✅ | ✅ | ❌ |
| View All Orders | ❌ | ❌ | ✅ | ❌ |
| Manage Users | ❌ | ❌ | ✅ | ❌ |
| Seller Dashboard | ❌ | ✅ | ❌ | ❌ |

---

## 💡 Pro Tips

### 1. Pagination
All list endpoints support pagination:
```
GET /api/products?page=2&per_page=20
```

### 2. Eager Loading
Relationships are automatically loaded for better performance

### 3. Stock Management
- Stock is validated before adding to cart
- Stock is decremented on order creation
- Stock is restored on order cancellation

### 4. Reviews
- Users can only review products once
- Reviews are marked as "verified purchase" if user bought the product
- Average rating is automatically calculated

### 5. Order Status Flow
```
pending → processing → shipped → delivered
        ↓
    cancelled (can cancel if pending/processing)
```

---

## 📚 Full Documentation

For complete API documentation, see:
- **API_DOCUMENTATION.md** - All endpoints with examples
- **IMPLEMENTATION_COMPLETE.md** - Feature overview

---

## 🐛 Troubleshooting

### Issue: 401 Unauthorized
- **Solution**: Make sure token is included in Authorization header
- **Format**: `Authorization: Bearer YOUR_TOKEN`

### Issue: 403 Forbidden
- **Solution**: Check user role permissions
- **Example**: Sellers can't access admin endpoints

### Issue: 422 Validation Error
- **Solution**: Check request body matches required fields
- **Tip**: Response will show which fields are invalid

### Issue: Stock Insufficient
- **Solution**: Product doesn't have enough stock
- **Check**: GET /api/products/{id} to see current stock

---

## 🎉 You're Ready!

All features are implemented and working:
✅ Shopping Cart & Checkout
✅ Seller Dashboard
✅ Order Management
✅ Advanced Features (Reviews, Analytics, Search)

Start building your frontend or test the API with Postman!

**Happy Coding! 🚀**
