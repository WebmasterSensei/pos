<?php

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');

Route::get('/home', [PosController::class, 'index'])->name('pos.index');
Route::post('/scanning-barcode', [PosController::class, 'scanningBarcode'])->name('scanningBarcode');
Route::post('/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

// Inventory
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
Route::post('/inventory/{id}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');

// Transactions
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
