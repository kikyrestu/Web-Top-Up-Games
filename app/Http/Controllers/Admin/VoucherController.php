<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        return view('admin.vouchers.form', ['voucher' => new Voucher()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'              => 'required|string|max:30|unique:vouchers,code',
            'description'       => 'nullable|string|max:255',
            'type'              => 'required|in:percentage,flat',
            'value'             => 'required|numeric|min:0',
            'max_discount'      => 'nullable|numeric|min:0',
            'min_purchase'      => 'required|numeric|min:0',
            'max_uses'          => 'nullable|integer|min:1',
            'max_uses_per_user' => 'required|integer|min:1',
            'is_active'         => 'nullable|boolean',
            'starts_at'         => 'nullable|date',
            'expires_at'        => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['code']      = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);

        Voucher::create($data);
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil dibuat!');
    }

    public function edit(Voucher $voucher)
    {
        return view('admin.vouchers.form', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'code'              => 'required|string|max:30|unique:vouchers,code,' . $voucher->id,
            'description'       => 'nullable|string|max:255',
            'type'              => 'required|in:percentage,flat',
            'value'             => 'required|numeric|min:0',
            'max_discount'      => 'nullable|numeric|min:0',
            'min_purchase'      => 'required|numeric|min:0',
            'max_uses'          => 'nullable|integer|min:1',
            'max_uses_per_user' => 'required|integer|min:1',
            'is_active'         => 'nullable|boolean',
            'starts_at'         => 'nullable|date',
            'expires_at'        => 'nullable|date',
        ]);

        $data['code']      = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        $voucher->update($data);
        return back()->with('success', 'Voucher berhasil diperbarui!');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return back()->with('success', 'Voucher dihapus.');
    }

    /**
     * AJAX: validate voucher code (used in checkout form).
     */
    public function validate(Request $request)
    {
        $code     = strtoupper(trim($request->code));
        $subtotal = (float) $request->subtotal;
        $userId   = auth()->id();

        $result = app(\App\Services\VoucherService::class)->apply($code, $subtotal, $userId);

        return response()->json([
            'success'  => $result['success'],
            'message'  => $result['message'],
            'discount' => $result['discount'] ?? 0,
        ]);
    }
}
