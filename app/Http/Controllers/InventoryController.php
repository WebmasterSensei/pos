<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function getProducts()
    {
        $path = Product::join('categories', 'categories.id', 'products.category')->paginate(10);
        return $path;
    }
    private function getProductForSearch()
    {
        $path = Product::get();
        return $path;
    }

    private function getCategories()
    {
        return Category::all();
    }


    public function index()
    {
        $products = $this->getProducts();
        $categories = $this->getCategories();

        $total = $this->getProductForSearch()->count();

        $low = $products->filter(function ($p) {
            return $p->stock > 0 && $p->stock <= 5;
        })->count();

        $out = $products->filter(function ($p) {
            return $p->stock <= 0;
        })->count();

        return view('inventory.index', compact('products', 'categories', 'total', 'low', 'out'));
    }

    public function store(Request $request)
    {
        $products = $this->getProductForSearch();

        foreach ($products as $p) {
            if ($p->barcode === $request->barcode) {
                return response()->json(['error' => 'Barcode already exists'], 422);
            }
        }
        $product = Product::create([
            'product_name'     => $request->name,
            'price'    => (float)$request->price,
            'cost'     => (float)($request->cost ?? 0),
            'stock'    => (int)$request->stock,
            'category' => $request->category_id ?? 'General',
            'barcode'  => $request->barcode,
            'images'    => $request->image ?? '📦',
        ]);

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
