<?php

namespace App\Http\Controllers;

use App\Category;
use App\Checkout;
use App\Product;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = $request->input('category');
        $query = $request->input('query');
        $products = $this->getProducts($categoryId, $query);

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
        $tr_no = Checkout::count();


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
        $itemIds = collect($data['items'])->pluck('id')->toArray();


        $transaction = Checkout::create([
            'tr_no'       => $tr_no + 1,
            'items'      =>  $itemIds,
            'item_count' => collect($data['items'])->sum('qty'),
            'subtotal'   => $subtotal,
            'discount'   => $discountVal,
            'tax'        => $tax,
            'total'      => $data['total'],
            'cash'       => $data['cash'],
            'change'     => $data['change'],
        ]);


        $transactions[] = $transaction;
        // Deduct stock

        foreach ($request['items'] as $item) {
            $product = Product::find($item['id']); // Use find() for a single record
            if ($product) {
                $product->stock -= $item['qty']; // Deduct the requested stock
                if ($product->stock < 0) {
                    $product->stock = 0; // Prevent negative stock
                }
                $product->save(); // Save the changes
            }
        }

        return response()->json(['success' => true, 'transaction' => $transaction]);
    }

    // ===== Helpers =====
    private function getProducts($cat_id, $query)
    {
        // dd($cat_id);
        $path = Product::join('categories', 'categories.id', 'products.category')
            ->when(isset($cat_id), function ($q) use ($cat_id) {
                $q->where('categories.id', $cat_id);
            })
            ->when(isset($query), function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('products.product_name', 'like', "%{$query}%")
                        ->orWhere('products.barcode', 'like', "%{$query}%");
                });
            })
            ->paginate(12);
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

    // private function defaultProducts(): array
    // {
    //     return [
    //         ['id' => '1', 'name' => 'Coca-Cola 355ml', 'price' => 35, 'cost' => 20, 'stock' => 50, 'category' => 'Beverages', 'barcode' => '4901085615324', 'emoji' => '🥤'],
    //         ['id' => '2', 'name' => 'Pepsi 355ml', 'price' => 35, 'cost' => 20, 'stock' => 48, 'category' => 'Beverages', 'barcode' => '4900016108346', 'emoji' => '🥤'],
    //         ['id' => '3', 'name' => 'Mineral Water 500ml', 'price' => 20, 'cost' => 10, 'stock' => 100, 'category' => 'Beverages', 'barcode' => '4800016100012', 'emoji' => '💧'],
    //         ['id' => '4', 'name' => 'Lay\'s Classic', 'price' => 55, 'cost' => 35, 'stock' => 30, 'category' => 'Snacks', 'barcode' => '4800016200011', 'emoji' => '🍟'],
    //         ['id' => '5', 'name' => 'Oreo Original', 'price' => 45, 'cost' => 28, 'stock' => 40, 'category' => 'Snacks', 'barcode' => '7622210011138', 'emoji' => '🍪'],
    //         ['id' => '6', 'name' => 'Kit Kat 2-finger', 'price' => 25, 'cost' => 15, 'stock' => 60, 'category' => 'Snacks', 'barcode' => '6281002700015', 'emoji' => '🍫'],
    //         ['id' => '7', 'name' => 'Instant Noodles', 'price' => 12, 'cost' => 7, 'stock' => 80, 'category' => 'Food', 'barcode' => '4800029130020', 'emoji' => '🍜'],
    //         ['id' => '8', 'name' => 'Canned Sardines', 'price' => 28, 'cost' => 18, 'stock' => 55, 'category' => 'Food', 'barcode' => '4800146010069', 'emoji' => '🐟'],
    //         ['id' => '9', 'name' => 'Rice (1kg)', 'price' => 65, 'cost' => 50, 'stock' => 20, 'category' => 'Food', 'barcode' => '4807112001001', 'emoji' => '🌾'],
    //         ['id' => '10', 'name' => 'Shampoo Sachet', 'price' => 8, 'cost' => 4, 'stock' => 200, 'category' => 'Personal Care', 'barcode' => '4800051250190', 'emoji' => '🧴'],
    //         ['id' => '11', 'name' => 'Soap Bar 135g', 'price' => 45, 'cost' => 28, 'stock' => 40, 'category' => 'Personal Care', 'barcode' => '6941028090083', 'emoji' => '🧼'],
    //         ['id' => '12', 'name' => 'Toothpaste 75ml', 'price' => 55, 'cost' => 35, 'stock' => 25, 'category' => 'Personal Care', 'barcode' => '6914088820018', 'emoji' => '🦷'],
    //         ['id' => '13', 'name' => 'Detergent Sachet', 'price' => 10, 'cost' => 6, 'stock' => 150, 'category' => 'Household', 'barcode' => '4800086600011', 'emoji' => '🧹'],
    //         ['id' => '14', 'name' => 'Dishwashing Liquid', 'price' => 35, 'cost' => 22, 'stock' => 30, 'category' => 'Household', 'barcode' => '4800086700018', 'emoji' => '🫧'],
    //         ['id' => '15', 'name' => 'AA Batteries 2pcs', 'price' => 65, 'cost' => 40, 'stock' => 4, 'category' => 'Electronics', 'barcode' => '6941028070012', 'emoji' => '🔋'],
    //         ['id' => '16', 'name' => 'Phone Load ₱50', 'price' => 50, 'cost' => 45, 'stock' => 0, 'category' => 'Services', 'barcode' => '9999999999991', 'emoji' => '📱'],
    //     ];
    // }
}
