<?php

namespace Tests\Clients;

use App\Clients\BestBuy;
use App\Models\Stock;
use Database\Seeders\RetailerWithProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @group api
 */
class BestBuyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_tracks_a_product()
    {
        $this->seed(RetailerWithProductSeeder::class);

        $stock = tap(Stock::first())->update([
            'sku' => '6522225', // Nintendo Switch sku
            'url' => 'https://www.bestbuy.com/site/nintendo-switch-with-neon-blue-and-neon-red-joycon/6522225.p?skuId=6522225',
        ]);

        try {
            (new BestBuy())->checkAvailability($stock);

        } catch (\Exception $e) {
            $this->fail('Failed to track the BestBuy API properly. ' . $e->getMessage());
        }

        $this->assertTrue(true);
    }

    /** @test */
    public function it_creates_proper_the_proper_stock_status_response()
    {
        Http::fake(fn() => ['salePrice' => 299.99, 'onlineAvailability' => true]);
        $stockStatus = (new BestBuy())->checkAvailability(new Stock());

        $this->assertEquals(29999, $stockStatus->price);
        $this->assertTrue(true, $stockStatus->available);
    }
}
