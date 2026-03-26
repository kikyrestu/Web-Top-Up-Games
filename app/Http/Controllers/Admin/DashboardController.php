<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Category;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Transaction::where('transaction_status', 'success')->sum('total_amount');
        $totalOrders = Transaction::count();
        $successfulOrders = Transaction::where('transaction_status', 'success')->count();
        $pendingOrders = Transaction::where('transaction_status', 'pending')->count();
        
        $recentTransactions = Transaction::with(['items.product'])->orderBy('created_at', 'desc')->take(10)->get();
        $totalProducts = Product::count();
        $totalCategories = Category::count();

        return view('admin.dashboard.index', compact(
            'totalRevenue', 'totalOrders', 'successfulOrders', 'pendingOrders',
            'recentTransactions', 'totalProducts', 'totalCategories'
        ));
    }
}
