<?php

namespace App\Http\Controllers;

use App\CheckOut;
use App\Exports\InvoicesExport as ExportsInvoicesExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelWriter;

class TransactionController extends Controller
{
    public function index(Request $request)
    {


        $date = $request->input('date');

        $transactions = CheckOut::orderBy('created_at', 'desc')
            ->when(isset($date), function ($q) use ($date) {
                $q->where(function ($sub) use ($date) {
                    $sub->where('check_outs.created_at', 'like', "%{$date}%");
                });
            })->paginate(10);

        // Calculate totals
        $data = CheckOut::get();
        $totalRevenue = $data->sum('total');
        $totalTransactions = $data->count();
        $itemCount = $data->sum('item_count');
        $avgSale = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;


        // Pass all to view
        return view('transactions.index', compact('transactions', 'totalRevenue', 'totalTransactions', 'itemCount', 'avgSale'));
    }

    public function downloadExcel(Request $request, ExcelWriter $excel)
    {
        $query = $request->input('date'); // or 'date', depends on your form
        return $excel->download(new ExportsInvoicesExport($query), 'invoices.xlsx');
    }
}
