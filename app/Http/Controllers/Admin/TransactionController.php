<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::latest();

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', 'like', '%' . $request->customer_id . '%');
        }
        if ($request->filled('status')) {
            $query->where('transaction_status', $request->status);
        }

        $transactions = $query->paginate(15)->withQueryString();

        return view('admin.transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'transaction_status' => 'required|in:PENDING,PAID,PROCESSING,SUCCESS,FAILED,EXPIRED',
        ]);

        $transaction->update([
            'transaction_status' => $request->transaction_status
        ]);

        return redirect()->back()->with('success', 'Status transaksi berhasil diperbarui.');
    }
}
