⚡ Laravel Flash Sale API

A robust, high-concurrency API designed to handle flash sales with strict inventory control. This system guarantees no overselling under burst traffic, ensures idempotent payments, and manages short-lived inventory holds effectively.

🧠 Assumptions & Invariants Enforced

1. Concurrency Control (The "No Overselling" Rule)

Invariant: Product stock must never drop below zero, regardless of traffic load.

Enforcement:

Pessimistic Locking (lockForUpdate): Applied critically during Hold creation and Order processing. This forces parallel requests to queue at the database level, preventing race conditions.

Atomic Transactions: All state changes (Stock deduction + Hold creation) occur within a single database transaction.

2. Inventory Holds (Temporary Reservation)

Assumption: A "Hold" reserves stock for exactly 2 minutes.

Lifecycle:

Active: Stock is deducted immediately upon Hold creation.

Expired: If not converted to an Order, a scheduled background job (HoldRelease) releases the stock back to the pool every minute.

Converted: When an Order is created, the Hold is deleted to prevent double usage.

3. Payment Safety (Idempotency)

Invariant: A single payment transaction ID must strictly be processed once.

Enforcement:

Unique Constraint: The payments table enforces a unique transaction_id.

Check-First Pattern: The webhook endpoint verifies existence before processing. Duplicate webhooks return 200 OK instantly without side effects.

Restocking on Failure: If a payment fails or an order is cancelled, stock is immediately returned to the database.

🚀 How to Run the App

1. Setup Environment

# Clone and install dependencies
git clone <repo_url>
cd flash_sale
composer install

# Environment configuration
cp .env.example .env
php artisan key:generate

# Database Setup (Ensure MySQL is running)
# Update .env with your DB credentials first
php artisan migrate --seed


2. Run the Server & Background Workers

You need two terminals running:

Terminal 1: The API Server

php artisan serve


Terminal 2: The Scheduler (For Hold Expiry) This runs the cleanup job to release expired stock.

php artisan schedule:work


🧪 How to Run Tests

Prerequisites

Create a dedicated testing database named flash_sale_testing in your MySQL.

1. Run Logic & Feature Tests

Covers hold creation, expiry logic, and webhook idempotency.

php artisan test


2. Run Concurrency Stress Test (The "Race" Test)

A custom command simulating 30 concurrent requests competing for limited stock.

Goal: Prove that stock reaches exactly 0 without negative values.

Command: (Ensure server is running on port 8000)

php artisan test:race


📊 Logs & Metrics

The system uses structured logging for observability.

Log Location

Logs are written to the daily channel to avoid clutter:

Path: storage/logs/laravel.log (or flash_sale.log if configured).

Key Metrics to Watch

Search the logs for these tags to monitor system health:

[INFO] ✅ Payment Success: Successful transactions.

[INFO] ♻️ Duplicate Webhook ignored: Evidence of idempotency protection.

[WARNING] 📉 Out of Stock hit: Indicates high contention on a product.

[INFO] 🔄 Released hold: Evidence of the background worker cleaning up expired holds.

🔗 API Reference

Method

Endpoint

Payload

Description

GET

/api/products/{id}

-

Get product details (Cached).

POST

/api/holds

{ "product_id": 1, "quantity": 1 }

Create a 2-minute stock reservation.

POST

/api/orders

{ "hold_id": "ULID..." }

Convert a hold into a pending order.

POST

/api/payments/webhook

`{ "transaction_id": "...", "status": "success"}

