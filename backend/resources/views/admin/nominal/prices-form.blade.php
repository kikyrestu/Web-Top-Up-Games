<x-layouts.app :title="($formMode === 'create' ? 'Tambah Provider Price' : 'Edit Provider Price')">
    <div class="grid" style="max-width: 920px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah Provider Price' : 'Edit Provider Price' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.nominal.prices.store') : route('admin.nominal.prices.update', ['price' => $row->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="provider_id">Provider</label>
                        <select id="provider_id" name="provider_id" required>
                            <option value="">Pilih provider</option>
                            @foreach ($providers as $provider)
                                <option value="{{ (int) $provider->id }}" @selected((int) old('provider_id', $row->provider_id) === (int) $provider->id)>{{ $provider->code }} - {{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="product_id">Product</label>
                        <select id="product_id" name="product_id" required>
                            <option value="">Pilih product</option>
                            @foreach ($products as $product)
                                <option value="{{ (int) $product->id }}" @selected((int) old('product_id', $row->product_id) === (int) $product->id)>{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr 1fr;">
                    <div>
                        <label for="base_price">Base Price</label>
                        <input id="base_price" name="base_price" type="number" min="0" step="0.01" value="{{ old('base_price', $row->base_price) }}" required>
                    </div>
                    <div>
                        <label for="admin_fee">Admin Fee</label>
                        <input id="admin_fee" name="admin_fee" type="number" min="0" step="0.01" value="{{ old('admin_fee', $row->admin_fee ?? 0) }}">
                    </div>
                    <div>
                        <label for="commission">Commission</label>
                        <input id="commission" name="commission" type="number" min="0" step="0.01" value="{{ old('commission', $row->commission ?? 0) }}">
                    </div>
                </div>

                <div>
                    <label for="provider_updated_at">Provider Updated At</label>
                    <input id="provider_updated_at" name="provider_updated_at" type="datetime-local" value="{{ old('provider_updated_at', $row->provider_updated_at?->format('Y-m-d\\TH:i')) }}">
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $row->is_active ?? true))>
                    Active
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.nominal.prices.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
