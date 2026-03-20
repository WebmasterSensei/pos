<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private function getProducts()
    {
        $path = Product::join('categories', 'categories.id', 'products.category')->orderByDesc('products.created_at')->paginate(10);
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
                 return response()->json(['error' => true, '' => $p]);
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
        // Find the product by ID
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        // Update the product
        $product->update([
            'product_name' => $request->name,
            'price'        => (float)$request->price,
            'cost'         => (float)($request->cost ?? 0),
            'stock'        => (int)$request->stock,
            'category'     => $request->category_id,
            'barcode'      => $request->barcode,
            'images'       => $request->image ?? '📦',
        ]);

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
