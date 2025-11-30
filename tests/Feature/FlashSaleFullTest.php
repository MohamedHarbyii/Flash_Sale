<?php

namespace Tests\Feature;

use App\Jobs\HoldRelease;
use App\Models\Hold;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FlashSaleFullTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Requirement: Hold expiry returns availability
     * اختبار أن انتهاء مدة الحجز يعيد الكمية للمخزون
     */
    public function test_expired_holds_return_stock_availability()
    {
        // 1. نجهز منتج فيه قطعة واحدة
        $product = Product::factory()->create(['stock' => 1]);

        // 2. نحجز القطعة (المخزون يصبح 0)
        $this->postJson('/api/holds', [
            'product_id' => $product->id,
            'quantity' => 1
        ])->assertStatus(201); // تأكد أن الكود عندك يرجع 201

        $this->assertEquals(0, $product->fresh()->stock);

        // 3. نعدل تاريخ الحجز ليكون قديماً (انتهى منذ 5 دقائق)
        $hold = Hold::first();
        $hold->update(['expires_at' => now()->subMinutes(5)]);

        // 4. نشغل الـ Job يدوياً (محاكاة للجدولة)
        // بما أنك تستخدم Job وليس Command، ننفذها هكذا:
        (new HoldRelease)->handle();

        // 5. نتأكد أن المخزون عاد 1 مرة أخرى
        $this->assertEquals(1, $product->fresh()->stock);
        // نتأكد أن الحجز تم حذفه
        $this->assertDatabaseCount('holds', 0);
    }

    /**
     * Requirement: Webhook idempotency (same key repeated)
     * اختبار أن تكرار نفس الويب هوك لا يكرر العملية
     */
    public function test_webhook_idempotency_handles_duplicates()
    {
        // 1. تجهيز البيانات
        $product = Product::factory()->create(['stock' => 10]);
        // ننشئ أوردر وهمي حالته Pending
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'hold_id' => \Illuminate\Support\Str::ulid(), // <--- ضيف السطر ده
            'status' => PaymentStatus::Pending, // تأكد أن هذه هي القيمة الافتراضية عندك
            'quantity' => 1,
            'total_price' => 100
        ]);

        $payload = [
            'transaction_id' => 'TXN_TEST_UNIQUE_123',
            'order_id' => $order->id,
            'status' => 'success',
            'amount' => 100
        ];

        // 2. المحاولة الأولى: يجب أن تنجح وتحدث الأوردر
        $this->postJson('/api/payments/webhook', $payload)
             ->assertStatus(200);

        // نتأكد أن الأوردر تحول لـ Paid (أو القيمة المستخدمة في PaymentStatus::Payed)
        $this->assertNotEquals('pending_payment', $order->fresh()->status);

        // 3. المحاولة الثانية بنفس البيانات: يجب أن تنجح (200 OK) ولكن برسالة مختلفة
        $response = $this->postJson('/api/payments/webhook', $payload);
        
        $response->assertStatus(200);
        // نتأكد أن الرسالة تدل على أنه تمت معالجته مسبقاً (حسب كودك في PaymentController)
        // غالباً الرسالة عندك "processed before" أو ما شابه
        // $response->assertJsonFragment(['message' => 'processed before']); 

        // 4. نتأكد أن الدفع لم يسجل مرتين في جدول المدفوعات
        $this->assertDatabaseCount('payments', 1);
    }

    /**
     * Requirement: Webhook arriving before order creation
     * اختبار وصول ويب هوك لأوردر غير موجود
     */
    public function test_webhook_handles_non_existent_order()
    {
        $payload = 
        [
            'transaction_id' => 'TXN_GHOST',
            'order_id' => (string) Str::ulid(), // ID غير موجود
            'status' => 'success',
            'amount' => 100
        ];

        // المفروض يرجع 404 لأننا استخدمنا findOrFail أو firstOrFail
        $response = $this->postJson('/api/payments/webhook', $payload);

        $response->assertStatus(404);
    }
}