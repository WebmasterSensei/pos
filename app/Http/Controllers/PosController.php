<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $products = $this->getProducts();

        $categories = $this->getCategories();

        return view('pos.index', compact('products', 'categories'));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'items'   => 'required|array',
            'total'   => 'required|numeric',
            'cash'    => 'required|numeric',
            'change'  => 'required|numeric',
        ]);

        // Load existing transactions
        $transactions = $this->getTransactions();

        // Calculate totals
        $subtotal = collect($data['items'])->sum(function ($i) {
            return $i['price'] * $i['qty'];
        });
        $discountVal = 0;
        if (isset($data['discount'])) {
            $d = $data['discount'];
            $discountVal = $d['type'] === 'percent'
                ? $subtotal * ($d['val'] / 100)
                : $d['val'];
        }
        $taxable = $subtotal - $discountVal;
        $tax = $taxable * 0.12;

        $transaction = [
            'id'         => count($transactions) + 1,
            'items'      => $data['items'],
            'item_count' => collect($data['items'])->sum('qty'),
            'subtotal'   => $subtotal,
            'discount'   => $discountVal,
            'tax'        => $tax,
            'total'      => $data['total'],
            'cash'       => $data['cash'],
            'change'     => $data['change'],
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];

        $transactions[] = $transaction;
        $this->saveTransactions($transactions);

        // Deduct stock
        $products = $this->getProducts();


        foreach ($data['items'] as $item) {
            foreach ($products as &$product) {
                if ($product['id'] == $item['id']) {
                    $product['stock'] = max(0, $product['stock'] - $item['qty']);
                    break;
                }
            }
        }
        $this->saveProducts($products);

        return response()->json(['success' => true, 'transaction' => $transaction]);
    }

    // ===== Helpers =====
    private function getProducts()
    {
        $path = Product::get();
        return $path;
    }

    private function getCategories()
    {
        $path = Category::get();
        return $path;
    }

    public function scanningBarcode(Request $request)
    {
        $code = $request->input('code');
        $product = Product::where('barcode', $code)->first();

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => "Barcode not found: $code"], 404);
        }

        if ($product->stock <= 0) {
            return response()->json(['status' => 'error', 'message' => "{$product->name} is out of stock"], 200);
        }

        // Optionally add to cart logic here

        return response()->json(['status' => 'success', 'message' => "Scanned: {$product->name}", 'product' => $product]);
    }

    private function saveProducts(array $products): void
    {
        file_put_contents(storage_path('app/products.json'), json_encode($products, JSON_PRETTY_PRINT));
    }

    private function getTransactions(): array
    {
        $path = storage_path('app/transactions.json');
        if (!file_exists($path)) return [];
        return json_decode(file_get_contents($path), true) ?? [];
    }

    private function saveTransactions(array $txs): void
    {
        file_put_contents(storage_path('app/transactions.json'), json_encode($txs, JSON_PRETTY_PRINT));
    }

    private function defaultProducts(): array
    {
        return [
            ['id' => '1', 'name' => 'Coca-Cola 355ml', 'price' => 35, 'cost' => 20, 'stock' => 50, 'category' => 'Beverages', 'barcode' => '4901085615324', 'emoji' => '🥤'],
            ['id' => '2', 'name' => 'Pepsi 355ml', 'price' => 35, 'cost' => 20, 'stock' => 48, 'category' => 'Beverages', 'barcode' => '4900016108346', 'emoji' => '🥤'],
            ['id' => '3', 'name' => 'Mineral Water 500ml', 'price' => 20, 'cost' => 10, 'stock' => 100, 'category' => 'Beverages', 'barcode' => '4800016100012', 'emoji' => '💧'],
            ['id' => '4', 'name' => 'Lay\'s Classic', 'price' => 55, 'cost' => 35, 'stock' => 30, 'category' => 'Snacks', 'barcode' => '4800016200011', 'emoji' => '🍟'],
            ['id' => '5', 'name' => 'Oreo Original', 'price' => 45, 'cost' => 28, 'stock' => 40, 'category' => 'Snacks', 'barcode' => '7622210011138', 'emoji' => '🍪'],
            ['id' => '6', 'name' => 'Kit Kat 2-finger', 'price' => 25, 'cost' => 15, 'stock' => 60, 'category' => 'Snacks', 'barcode' => '6281002700015', 'emoji' => '🍫'],
            ['id' => '7', 'name' => 'Instant Noodles', 'price' => 12, 'cost' => 7, 'stock' => 80, 'category' => 'Food', 'barcode' => '4800029130020', 'emoji' => '🍜'],
            ['id' => '8', 'name' => 'Canned Sardines', 'price' => 28, 'cost' => 18, 'stock' => 55, 'category' => 'Food', 'barcode' => '4800146010069', 'emoji' => '🐟'],
            ['id' => '9', 'name' => 'Rice (1kg)', 'price' => 65, 'cost' => 50, 'stock' => 20, 'category' => 'Food', 'barcode' => '4807112001001', 'emoji' => '🌾'],
            ['id' => '10', 'name' => 'Shampoo Sachet', 'price' => 8, 'cost' => 4, 'stock' => 200, 'category' => 'Personal Care', 'barcode' => '4800051250190', 'emoji' => '🧴'],
            ['id' => '11', 'name' => 'Soap Bar 135g', 'price' => 45, 'cost' => 28, 'stock' => 40, 'category' => 'Personal Care', 'barcode' => '6941028090083', 'emoji' => '🧼'],
            ['id' => '12', 'name' => 'Toothpaste 75ml', 'price' => 55, 'cost' => 35, 'stock' => 25, 'category' => 'Personal Care', 'barcode' => '6914088820018', 'emoji' => '🦷'],
            ['id' => '13', 'name' => 'Detergent Sachet', 'price' => 10, 'cost' => 6, 'stock' => 150, 'category' => 'Household', 'barcode' => '4800086600011', 'emoji' => '🧹'],
            ['id' => '14', 'name' => 'Dishwashing Liquid', 'price' => 35, 'cost' => 22, 'stock' => 30, 'category' => 'Household', 'barcode' => '4800086700018', 'emoji' => '🫧'],
            ['id' => '15', 'name' => 'AA Batteries 2pcs', 'price' => 65, 'cost' => 40, 'stock' => 4, 'category' => 'Electronics', 'barcode' => '6941028070012', 'emoji' => '🔋'],
            ['id' => '16', 'name' => 'Phone Load ₱50', 'price' => 50, 'cost' => 45, 'stock' => 0, 'category' => 'Services', 'barcode' => '9999999999991', 'emoji' => '📱'],
        ];
    }
}
