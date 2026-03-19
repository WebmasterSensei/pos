<?php

namespace App\Http\Controllers;

class TransactionController extends Controller
{
    public function index()
    {
        $path = storage_path('app/transactions.json');
        $transactions = file_exists($path)
            ? (json_decode(file_get_contents($path), true) ?? [])
            : [];

        // Sort newest first
        usort($transactions, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        return view('transactions.index', compact('transactions'));
    }
}
