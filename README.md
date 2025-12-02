# Laravel Flash Sale System ⚡

A robust, high-concurrency Flash Sale system designed to handle high-traffic inventory reservation and purchasing without overselling.

## 1. Assumptions & Invariants

The system is built upon strict rules to ensure data integrity during concurrent requests:

### 🛡️ No Overselling (Inventory Integrity)
* **Invariant:** Product stock can never drop below zero.
* **Mechanism:** Stock deduction and "Hold" creation occur atomically. The system utilizes database locking (likely `lockForUpdate` in the underlying logic) to serialize concurrent requests for the same product, ensuring that `stock` is only decremented if sufficient quantity exists.
* **Verification:** Validated via the `php artisan test:race` concurrency stress test.

### ⏳ Temporary Inventory Holds (TTL)
* **Assumption:** Items added to a cart/hold are reserved temporarily.
* **Lifecycle:**
    1.  **Reserve:** User requests a hold -> Stock is decremented immediately -> `Hold` record created with an `expires_at` timestamp.
    2.  **Release:** If the hold is not converted to an Order before expiration, the `HoldRelease` job returns the quantity to the product's stock and deletes the hold record.

### 💳 Payment Idempotency
* **Invariant:** A specific payment transaction (Webhook) is processed exactly once.
* **Mechanism:** The `PaymentController` checks if the `transaction_id` already exists in the database. Duplicate webhooks are ignored and logged as "processed before" to prevent double billing or order status corruption.

---

## 2. How to Run the App & Tests

### 🚀 Application Setup

1.  **Install Dependencies:**
    ```bash
    composer install
    ```
2.  **Environment Setup:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    # Configure your DB_DATABASE, DB_USERNAME, etc. in .env
    ```
3.  **Database Migration & Seeding:**
    ```bash
    php artisan migrate --seed
    ```
4.  **Start Background Queue (Crucial):**
    The system relies on queue workers to release expired stock.
    ```bash
    php artisan queue:work
    ```
5.  **Start the Server:**
    ```bash
    php artisan serve
    ```

### 🧪 Running Tests

**1. Functional & Feature Tests:**
Run the standard test suite (Pest/PHPUnit) to verify business logic, including hold expiry and webhook idempotency.
```bash
php artisan test
# OR
./vendor/bin/pest
```
**2\. Concurrency / Race Condition Test:** A custom Artisan command is available to simulate high-concurrency traffic (Race Conditions).

-   **Prerequisite:** Ensure the server is running (`php artisan serve`) at `http://127.0.0.1:8000`.
    
-   **Run the stress test:**
    
    Bash
    
        php artisan test:race
    
-   **What it does:** It resets the database, creates a product with limited stock (e.g., 10 items), and fires concurrent HTTP requests (e.g., 30 requests) to ensure the final stock is exactly 0 and no overselling occurred.
    

* * *

## 3\. Logs & Metrics (Observability)

### 📂 Application Logs

The system logs critical events to specific channels for auditing and debugging.

-   **Location:** `storage/logs/laravel.log` (or a dedicated `flash_sales.log` if configured in `config/logging.php`).
    
-   **What to look for:** The `PaymentController` explicitly logs to the `flash_sales` channel:
    
    -   `✅ Payment Success: {transaction_id}`
        
    -   `❌ Payment Failed: {transaction_id}`
        
    -   `♻️ Duplicate Webhook ignored: {transaction_id}`.
        

### 📊 Performance Metrics

For immediate feedback on system performance under load, use the output from the **Concurrency Test**:

-   **Successful requests:** Number of users who successfully secured stock.
    
-   **Failed requests:** Number of users rejected due to "Out of Stock".
    
-   **Final Stock:** Should match the expected invariant (0).
