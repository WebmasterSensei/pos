<?php

namespace App\Http\Controllers;

use App\CheckOut;

class TransactionController extends Controller
{
    public function index()
    {
        // Paginate transactions
        $transactions = CheckOut::orderBy('created_at', 'desc')->paginate(10);

        // Calculate totals
        $totalRevenue = $transactions->sum('total');
        $totalTransactions = $transactions->count();
        $itemCount = $transactions->sum('item_count');
        $avgSale = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Pass all to view
        return view('transactions.index', compact('transactions', 'totalRevenue', 'totalTransactions', 'itemCount', 'avgSale'));
    }
}
