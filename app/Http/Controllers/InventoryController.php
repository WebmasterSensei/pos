<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function getProducts(): array
    {
        $path = storage_path('app/products.json');
        if (!file_exists($path)) return [];
        return json_decode(file_get_contents($path), true) ?? [];
    }

    private function saveProducts(array $products): void
    {
        file_put_contents(storage_path('app/products.json'), json_encode($products, JSON_PRETTY_PRINT));
    }

    public function index()
    {
        $products = $this->getProducts();
        $categories = array_unique(array_column($products, 'category'));
        sort($categories);

        $total = count($products);

        $low = count(array_filter($products, function ($p) {
            return $p['stock'] > 0 && $p['stock'] <= 5;
        }));

        $out = count(array_filter($products, function ($p) {
            return $p['stock'] <= 0;
        }));


        return view('inventory.index', compact('products', 'categories','total', 'low', 'out'));
    }

    public function store(Request $request)
    {
        $products = $this->getProducts();

        $product = [
            'id'       => $request->id ?? (string)(time()),
            'name'     => $request->name,
            'price'    => (float)$request->price,
            'cost'     => (float)($request->cost ?? 0),
            'stock'    => (int)$request->stock,
            'category' => $request->category ?? 'General',
            'barcode'  => $request->barcode,
            'emoji'    => $request->emoji ?? '📦',
        ];

        // Check for duplicate barcode
        foreach ($products as $p) {
            if ($p['barcode'] === $product['barcode']) {
                return response()->json(['error' => 'Barcode already exists'], 422);
            }
        }

        $products[] = $product;
        $this->saveProducts($products);

        return response()->json(['success' => true, 'product' => $product]);
    }

    public function update(Request $request, string $id)
    {
        $products = $this->getProducts();

        foreach ($products as &$product) {
            if ($product['id'] == $id) {
                $product['name']     = $request->name ?? $product['name'];
                $product['price']    = (float)($request->price ?? $product['price']);
                $product['cost']     = (float)($request->cost ?? $product['cost']);
                $product['stock']    = (int)($request->stock ?? $product['stock']);
                $product['category'] = $request->category ?? $product['category'];
                $product['barcode']  = $request->barcode ?? $product['barcode'];
                $product['emoji']    = $request->emoji ?? $product['emoji'];
                break;
            }
        }

        $this->saveProducts($products);
        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        $products = $this->getProducts();
        $products = array_values(array_filter($products, function ($p) use ($id) {
            return $p['id'] != $id;
        }));
        $this->saveProducts($products);
        return response()->json(['success' => true]);
    }

    public function restock(Request $request, string $id)
    {
        $qty = (int)($request->qty ?? 0);
        $products = $this->getProducts();

        foreach ($products as &$product) {
            if ($product['id'] == $id) {
                $product['stock'] += $qty;
                break;
            }
        }

        $this->saveProducts($products);
        return response()->json(['success' => true]);
    }
}
