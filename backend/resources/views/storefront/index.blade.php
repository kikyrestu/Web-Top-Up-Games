<x-layouts.app :title="'TopUp Atlas - Checkout'">
    <div class="panel" style="display:grid; gap:16px;">
        <div>
            <h1>Checkout Top-Up & PPOB</h1>
            <p class="muted">Flow ini langsung pakai service Laravel yang sama dengan API backend.</p>
        </div>

        <form method="post" action="{{ route('storefront.checkout') }}" class="grid" style="grid-template-columns:1fr 1fr;">
            @csrf

            <div style="grid-column:1/-1;">
                <label for="product_id">Produk</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Pilih produk</option>
                    @foreach ($productsByCategory as $category => $products)
                        <optgroup label="{{ $category }}">
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                                    {{ $product->name }} ({{ $product->type }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="customer_target">Target Customer</label>
                <input id="customer_target" name="customer_target" type="text" value="{{ old('customer_target') }}" placeholder="User ID / Phone / Meter Number">
            </div>

            <div>
                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="number" min="1" max="10" value="{{ old('quantity', 1) }}">
            </div>

            <div>
                <label for="gateway">Payment Gateway</label>
                <select id="gateway" name="gateway" required>
                    @foreach ($gateways as $gateway)
                        <option value="{{ $gateway }}" @selected(old('gateway') === $gateway)>{{ $gateway }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="method">Metode Payment (opsional)</label>
                <input id="method" name="method" type="text" value="{{ old('method') }}" placeholder="VA / QRIS / E-Wallet">
            </div>

            <div style="grid-column:1/-1; display:flex; justify-content:flex-end;">
                <button class="btn" type="submit">Buat Order + Payment</button>
            </div>
        </form>
    </div>
</x-layouts.app>
