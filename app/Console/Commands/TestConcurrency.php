<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TestConcurrency extends Command
{
    protected $signature = 'test:race';
    protected $description = 'Simulate race condition on holds';

    public function handle()
    {
        $this->info('🚀 Preparing for launch...');

        // 1. نجهز الداتابيز (منتج واحد، مخزونه 10 قطع فقط)
        // بنمسح القديم عشان نبدأ على نضافة
        DB::table('holds')->truncate();
        DB::table('products')->truncate();
        
        $product = Product::create([
            'name' => 'Race Item',
            'price' => 100,
            'stock' => 10 // معانا 10 قطع بس
        ]);

        $this->info("📦 Product created with stock: {$product->stock}");
        $this->info("🔥 Firing 30 concurrent requests...");

        // 2. الهجوم المتوازي (Parallel Attack)
        // هنستخدم Http::pool عشان نبعت 30 ريكويست في نفس الوقت
        // الرابط لازم يكون رابط السيرفر بتاعك وهو شغال
        $url = 'http://127.0.0.1:8000/api/holds'; 

        $responses = Http::pool(fn ($pool) => array_map(function ($i) use ($pool, $url, $product) {
            return $pool->post($url, [
                'product_id' => $product->id,
                'quantity' => 1
            ]);
        }, range(1, 30))); // بنحاول نشتري 30 مرة

        // 3. تحليل النتائج
        $successful = 0;
        $failed = 0;

        foreach ($responses as $response) {
            if ($response->successful()) {
                $successful++;
            } else {
                $failed++;
            }
        }

        // 4. النتيجة النهائية
        $finalStock = $product->fresh()->stock;
        $totalHolds = DB::table('holds')->count();

        $this->newLine();
        $this->info("--- Results ---");
        $this->info("✅ Successful requests: $successful");
        $this->info("❌ Failed requests: $failed");
        $this->info("📉 Final Stock in DB: $finalStock");
        $this->info("🎫 Total Holds in DB: $totalHolds");

        // الحكم النهائي
        if ($finalStock === 0 && $totalHolds === 10) {
            $this->info("🎉 TEST PASSED! No overselling.");
        } else {
            $this->error("😱 TEST FAILED! Overselling detected.");
            $this->error("Expected Stock: 0, Found: $finalStock");
            $this->error("Expected Holds: 10, Found: $totalHolds");
        }
    }
}