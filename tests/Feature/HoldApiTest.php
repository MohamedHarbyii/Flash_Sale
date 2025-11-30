<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoldApiTest extends TestCase
{
    // التريت دي بتمسح الداتابيز وتبنيها من جديد قبل كل اختبار
    use RefreshDatabase; 

    /**
     * اختبار 1: الحجز الناجح
     */
    public function test_user_can_create_hold_if_stock_available()
    {
        // 1. Arrange: نجهز منتج
        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 100
        ]);

        // 2. Act: نبعت ريكويست حجز
        $response = $this->postJson('/api/holds', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        // 3. Assert: نتأكد من النتيجة
        $response->assertStatus(201); // Created
        
        // نتأكد إن المخزون نقص في الداتابيز
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 9 // كان 10 بقى 9
        ]);

        // نتأكد إن الحجز اتسجل
        $this->assertDatabaseCount('holds', 1);
    }

    /**
     * اختبار 2: فشل الحجز لعدم توفر المخزون
     */
    public function test_user_cannot_create_hold_if_stock_empty()
    {
        // 1. Arrange: منتج مخزونه قليل
        $product = Product::factory()->create([
            'stock' => 1
        ]);

        // 2. Act: نحاول نحجز 2 قطعة
        $response = $this->postJson('/api/holds', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        // 3. Assert: المفروض يرجع Error
        $response->assertStatus(409); // Out of stock status (حسب ما انت عاملها) Or 400
        
        // المخزون زي ما هو ماتغيرش
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 1
        ]);
    }
}