<?php

namespace App\Services;

use App\Models\ImportLog;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductImportService
{
    /**
     * Import products from a JSON array.
     *
     * Expected structure:
     * {
     *   "products": [
     *     {
     *       "name": "...",
     *       "link": "...",  // unique id
     *       "image-link": "...",
     *       "description": "...",
     *       "platform": "...",
     *       "category": "...",
     *       "tags": ["..."],
     *       "stores": [
     *         {
     *           "name": "...",
     *           "price": 1.23,
     *           "currency": "EUR",
     *           "region": "...",
     *           "link": "...",  // unique id
     *           "in_stock": true
     *         }
     *       ]
     *     }
     *   ]
     * }
     *
     * Logic:
     *   - Products: upsert by `link`. If exists, update; else create.
     *   - Offers: upsert by `link`. If exists, update; else create.
     *   - Stores: get-or-create by `name` (slug auto-generated).
     */
    public function import(array $data, string $source = 'manual'): ImportLog
    {
        $startedAt = microtime(true);
        $log = ImportLog::create([
            'source' => $source,
            'status' => 'pending',
            'payload' => $data,
        ]);

        $errors = [];
        $productsCreated = 0;
        $productsUpdated = 0;
        $offersCreated = 0;
        $offersUpdated = 0;

        $products = $data['products'] ?? [];
        if (!is_array($products)) {
            $log->update([
                'status' => 'failed',
                'errors' => ['Invalid payload: "products" must be an array'],
            ]);
            return $log;
        }

        $storeCache = [];

        try {
            DB::transaction(function () use (
                $products, &$errors, &$productsCreated, &$productsUpdated,
                &$offersCreated, &$offersUpdated, &$storeCache
            ) {
                foreach ($products as $i => $p) {
                    try {
                        $productResult = $this->importProduct($p, $storeCache, $errors, $i);
                        if ($productResult === 'created') $productsCreated++;
                        if ($productResult === 'updated') $productsUpdated++;
                    } catch (\Throwable $e) {
                        $errors[] = [
                            'index' => $i,
                            'product' => $p['name'] ?? null,
                            'error' => $e->getMessage(),
                        ];
                        Log::warning('import_product_failed', [
                            'index' => $i,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'errors' => array_merge($errors, [['fatal' => $e->getMessage()]]),
            ]);
            return $log;
        }

        $duration = (int) round((microtime(true) - $startedAt) * 1000);

        $status = empty($errors) ? 'success' : 'partial';

        $log->update([
            'status' => $status,
            'products_count' => count($products),
            'products_created' => $productsCreated,
            'products_updated' => $productsUpdated,
            'errors' => $errors,
            'duration_ms' => $duration,
        ]);

        return $log;
    }

    /**
     * Import a single product and its offers.
     * Returns 'created', 'updated', or null on failure.
     */
    protected function importProduct(array $data, array &$storeCache, array &$errors, int $index): ?string
    {
        // Validate required fields
        if (empty($data['link'])) {
            $errors[] = ['index' => $index, 'error' => 'Missing "link" for product'];
            return null;
        }
        if (empty($data['name'])) {
            $errors[] = ['index' => $index, 'error' => 'Missing "name" for product'];
            return null;
        }

        $link = $data['link'];
        $existing = Product::where('link', $link)->first();

        $productData = [
            'name' => $data['name'],
            'image_link' => $data['image-link'] ?? $data['image_link'] ?? null,
            'description' => $data['description'] ?? null,
            'platform' => $data['platform'] ?? null,
            'category' => $data['category'] ?? null,
            'tags' => $data['tags'] ?? [],
        ];

        if ($existing) {
            $existing->update($productData);
            $product = $existing;
            $result = 'updated';
        } else {
            $product = Product::create(array_merge(['link' => $link], $productData));
            $result = 'created';
        }

        // Import offers
        $stores = $data['stores'] ?? [];
        if (is_array($stores)) {
            foreach ($stores as $s) {
                $this->importOffer($product, $s, $storeCache, $errors, $index);
            }
        }

        return $result;
    }

    /**
     * Import a single offer for a product.
     */
    protected function importOffer(Product $product, array $data, array &$storeCache, array &$errors, int $productIndex): void
    {
        if (empty($data['link'])) {
            $errors[] = ['product_index' => $productIndex, 'error' => 'Missing "link" for offer'];
            return;
        }
        if (!isset($data['price']) || !is_numeric($data['price'])) {
            $errors[] = ['product_index' => $productIndex, 'offer_link' => $data['link'], 'error' => 'Missing/invalid "price" for offer'];
            return;
        }
        if (empty($data['currency'])) {
            $errors[] = ['product_index' => $productIndex, 'offer_link' => $data['link'], 'error' => 'Missing "currency" for offer'];
            return;
        }

        $link = $data['link'];

        // Get or create store
        $storeName = $data['name'] ?? 'Unknown';
        $storeId = $storeCache[$storeName] ?? null;
        if (!$storeId) {
            $store = Store::firstOrCreate(
                ['name' => $storeName],
                ['slug' => Str::slug($storeName)]
            );
            $storeId = $store->id;
            $storeCache[$storeName] = $storeId;
        }

        $offerData = [
            'product_id' => $product->id,
            'store_id' => $storeId,
            'price' => (float) $data['price'],
            'currency' => strtoupper($data['currency']),
            'region' => $data['region'] ?? null,
            'in_stock' => (bool) ($data['in_stock'] ?? true),
            'raw_data' => $data,
        ];

        Offer::updateOrCreate(
            ['link' => $link],
            $offerData
        );
    }
}
