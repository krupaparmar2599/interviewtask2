<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [InvoiceController::class, 'index'])->name('index');
Route::post('/invoice_save', [InvoiceController::class, 'save'])->name('invoice.save');
Route::get('/invoice/{id}', [InvoiceController::class, 'show'])->name('invoice.show');