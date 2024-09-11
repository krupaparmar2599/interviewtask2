<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use Validator;
use DB;

class InvoiceController extends Controller
{
    public function index()
    {

        $customer = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            // ->where('invoices.customer_id', $customerId)
            ->orderBy('invoices.created_at', 'desc')
            ->first(['invoices.*', 'customers.*', 'customers.id as customer_id', 'customers.name as customer_name', 'customers.email as customer_email']);

        return view('invoice.create', compact('customer'));
    }

    // public function create()
    // {
    //     return view('invoice.create');
    // }

    public function save(Request $request)
    {

        if ($request->is_existing == 0) {
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|unique:customers,email',
                'products.*.name' => 'required|string',
                'products.*.price' => 'required|numeric|min:0.01',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|unique:customers,email,' . $request->customer_id,
                'products.*.name' => 'required|string',
                'products.*.price' => 'required|numeric|min:0.01',
            ]);
        }
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->all()[0])->withInput();
        }

        if ($request->is_existing == 1) {
            $customer = Customer::where('email', $request->customer_email)->first();
            if ($customer) {
                $customer->name = $request->customer_name;
                $customer->total_items = $request->total_items;
                $customer->total_amount = $request->total_amount;
                $customer->total_discount_amount = $request->total_discount_amount;
                $customer->total_bill = $request->total_bill;
                $customer->save();
            } else {
            }
        } else {
            $customer = new Customer();
            $customer->name = $request->customer_name;
            $customer->email = $request->customer_email;
            $customer->total_items = $request->total_items;
            $customer->total_amount = $request->total_amount;
            $customer->total_discount_amount = $request->total_discount_amount;
            $customer->total_bill = $request->total_bill;
            $customer->save();
        }
        $totalItems = count($request->input('products'));
        $totalAmount = 0;
        $totalDiscountAmount = 0;

        foreach ($request->input('products') as $productData) {
            $price = $productData['price'];
            $discount = $productData['discount'] ?? 0;
            $amount = $price * (1 - $discount / 100);

            $totalAmount += $price;
            $totalDiscountAmount += $price * ($discount / 100);

            $invoice = new Invoice();
            $invoice->customer_id = $customer->id;
            $invoice->product_name = $productData['name'];
            $invoice->price = $price;
            $invoice->discount_percentage = $discount;
            $invoice->save();
        }

        $totalBill = $totalAmount - $totalDiscountAmount;
        return redirect()->route('index')
            ->with('success', 'Invoice created successfully!')
            ->with(compact('totalItems', 'totalAmount', 'totalDiscountAmount', 'totalBill'));
    }

    public function show($id)
    {
        $data = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->where('invoices.customer_id', $id)
            ->orderBy('invoices.created_at', 'desc')
            ->first(['invoices.*', 'customers.*', 'customers.id as customer_id', 'customers.name as customer_name', 'customers.email as customer_email']);
        return view('invoice.show', compact('data'));
    }
}
