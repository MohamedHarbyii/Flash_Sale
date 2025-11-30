# Laravel Flash Sale

---

1) Assumptions & invariants enforced

- No overselling
  - Invariant: product.stock must never drop below 0.
  - How enforced: stock deduction + hold creation happen atomically (CreateHold operation used by HoldController). Critical stock updates are protected by database transactions and row-level locking (lockForUpdate) in the hold/order flows.

- Short-lived inventory reservation (Holds)
  - Assumption: a Hold is a temporary reservation (project default: 2 minutes).
  - Lifecycle:
    - POST /api/holds deducts stock immediately and creates a Hold (expires_at set).
    - If converted to an Order (POST /api/orders) the Hold is consumed and removed.
    - Expired Holds are released by a scheduled job (HoldRelease) which returns stock to the product.

- Payment idempotency
  - Invariant: a payment transaction_id is applied only once.
  - How enforced: PaymentController checks PaymentDB::HasTransaction($transaction_id) and ignores duplicate webhooks (logs and returns without side effects). A UNIQUE constraint on payments.transaction_id is recommended/expected.

- Failure safety
  - Failed payments or manual cancellations restore stock (release the hold or increment product stock) within transactional flows.

---

2) How to run the app and tests (exact commands)

Prereqs: PHP 8.2+, Composer, MySQL (or the DB set in .env)

Clone & install
- git clone https://github.com/MohamedHarbyii/Flash_Sale.git
- cd Flash_Sale
- composer install

Environment
- cp .env.example .env
- php artisan key:generate
- Edit .env for DB and queue/log settings:
  - DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
  - QUEUE_CONNECTION (sync | database | redis)
  - LOG_CHANNEL

Database & seed
- php artisan migrate --seed

Run the app + scheduler (two terminals)
- Terminal A (API): php artisan serve
- Terminal B (scheduler for Hold expiry): php artisan schedule:work
Terminal C (queue work) run: php artisan queue:work

Tests
- Create a test DB (e.g., flash_sale_testing) and set DB_* in phpunit.xml or .env.testing.
- Run tests: php artisan test  (or vendor/bin/phpunit)
- Concurrency/stress checks: run php artisan test:race
- Use those to validate “no overselling” under concurrent requests.

---

3) Where to see logs & metrics

Logs
- u can see logs in the daily log I made logs/flash_sale
---

API (from routes/api.php)
- GET  /api/product/{id}        → ProductController@show (returns via cache layer)
- POST /api/holds               → HoldController@store (payload: product_id, quantity, optional user_id)
- POST /api/orders              → OrderController@store (create order)
- POST /api/payments/webhook    → PaymentController@store (payload: transaction_id, order_id, status)

Example cURL — create a hold
curl -s -X POST http://127.0.0.1:8000/api/holds \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":1,"user_id":123}'

Example cURL — payment webhook (idempotent)
curl -s -X POST http://127.0.0.1:8000/api/payments/webhook \
  -H "Content-Type: application/json" \
  -d '{"transaction_id":"txn_abc","order_id":1,"status":"success"}'

