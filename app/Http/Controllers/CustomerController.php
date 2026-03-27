<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\FavoriteGame;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
        $totalTransactions = $user->transactions()->count();
        $successTransactions = $user->transactions()->where('transaction_status', 'success')->count();
        $totalSpent = $user->transactions()->where('payment_status', 'paid')->sum('total_amount');
        
        $recentTransactions = $user->transactions()->latest()->take(5)->get();
        
        $favorites = $user->favoriteGames()->with('category')->get();

        return view('member.dashboard', compact(
            'user', 'totalTransactions', 'successTransactions', 'totalSpent', 'recentTransactions', 'favorites'
        ));
    }

    public function transactions(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->transactions()->latest();
        
        if ($request->filled('status')) {
            $status = $request->status;
            if (in_array($status, ['unpaid', 'paid', 'failed'])) {
                $query->where('payment_status', $status);
            } elseif (in_array($status, ['pending', 'processing', 'success'])) {
                $query->where('transaction_status', $status);
            }
        }
        
        $transactions = $query->paginate(10);
        
        return view('member.transactions', compact('transactions'));
    }

    public function transactionDetail($invoice)
    {
        $transaction = auth()->user()->transactions()
            ->with(['items', 'paymentGateway'])
            ->where('invoice_number', $invoice)
            ->firstOrFail();
            
        return view('member.transaction-detail', compact('transaction'));
    }

    public function profile()
    {
        $user = auth()->user();
        return view('member.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'whatsapp' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function favorites()
    {
        $favorites = auth()->user()->favoriteGames()->with('category')->get();
        return view('member.favorites', compact('favorites'));
    }

    public function toggleFavorite($categoryId)
    {
        $user = auth()->user();
        
        $existing = FavoriteGame::where('user_id', $user->id)->where('category_id', $categoryId)->first();
        
        if ($existing) {
            $existing->delete();
            return response()->json(['success' => true, 'is_favorite' => false, 'message' => 'Dihapus dari favorit.']);
        }
        
        FavoriteGame::create([
            'user_id' => $user->id,
            'category_id' => $categoryId,
        ]);
        
        return response()->json(['success' => true, 'is_favorite' => true, 'message' => 'Ditambahkan ke favorit.']);
    }
}
