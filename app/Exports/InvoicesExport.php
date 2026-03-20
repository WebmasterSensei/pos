<?php

namespace App\Exports;

use App\CheckOut;
use App\Product;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Date;

class InvoicesExport implements FromView, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function view(): View
    {
        return view('excels.invoice', [
            'invoices' => $this->getDataCheckout(),
            'date' => Date::parse($this->query)->toFormattedDateString()
        ]);
    }

    private function getDataCheckout()
    {
        // Get all checkouts for the date query
        $checkouts = CheckOut::where('created_at', 'like', '%' . $this->query . '%')->get();

        // Collect all product IDs from all checkouts
        $productIds = collect($checkouts)
            ->pluck('items')      // array of IDs
            ->flatten()           // flatten all arrays
            ->unique()            // remove duplicates
            ->toArray();

        // Get product names from the DB
        $allProducts = Product::whereIn('id', $productIds)
            ->pluck('product_name', 'id'); // [id => product_name]

        // Map checkout items to product names and quantities
        $checkouts->transform(function ($checkout) use ($allProducts) {
            if (is_array($checkout->items) && is_array($checkout->qty)) {
                // Map each product ID to name and corresponding qty
                $checkout->items = collect($checkout->items)
                    ->map(function ($productId, $index) use ($allProducts, $checkout) {
                        return [
                            'product_name' => $allProducts[$productId] ?? 'Unknown',
                            'qty' => $checkout->qty[$index] ?? 1, // use actual quantity from qty array
                        ];
                    })
                    ->toArray();
            }
            return $checkout;
        });

        return $checkouts;
    }
}
