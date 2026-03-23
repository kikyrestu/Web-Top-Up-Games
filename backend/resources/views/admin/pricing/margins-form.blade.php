<x-layouts.app :title="($formMode === 'create' ? 'Tambah Pricing Rule' : 'Edit Pricing Rule')">
    <div class="grid" style="max-width: 920px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah Pricing Rule (Margin)' : 'Edit Pricing Rule (Margin)' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.pricing.margins.store') : route('admin.pricing.margins.update', ['margin' => $row->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                @php
                    $defaultScope = $row->product_id ? 'PRODUCT' : ($row->category_id ? 'CATEGORY' : 'GLOBAL');
                    $selectedScope = old('scope', $defaultScope);
                @endphp

                <div>
                    <label for="scope">Scope</label>
                    <select id="scope" name="scope" required>
                        <option value="PRODUCT" @selected($selectedScope === 'PRODUCT')>PRODUCT</option>
                        <option value="CATEGORY" @selected($selectedScope === 'CATEGORY')>CATEGORY</option>
                        <option value="GLOBAL" @selected($selectedScope === 'GLOBAL')>GLOBAL</option>
                    </select>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="product_id">Product (jika scope PRODUCT)</label>
                        <select id="product_id" name="product_id">
                            <option value="">-</option>
                            @foreach ($products as $product)
                                <option value="{{ (int) $product->id }}" @selected((int) old('product_id', $row->product_id) === (int) $product->id)>{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="category_id">Category (jika scope CATEGORY)</label>
                        <select id="category_id" name="category_id">
                            <option value="">-</option>
                            @foreach ($categories as $category)
                                <option value="{{ (int) $category->id }}" @selected((int) old('category_id', $row->category_id) === (int) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="mode">Mode</label>
                        <select id="mode" name="mode" required>
                            <option value="FLAT" @selected(old('mode', strtoupper((string) ($row->mode ?: 'FLAT'))) === 'FLAT')>FLAT</option>
                            <option value="PERCENTAGE" @selected(old('mode', strtoupper((string) ($row->mode ?: 'FLAT'))) === 'PERCENTAGE')>PERCENTAGE</option>
                        </select>
                    </div>
                    <div>
                        <label for="value">Value</label>
                        <input id="value" name="value" type="number" min="0" step="0.01" value="{{ old('value', $row->value ?? 0) }}" required>
                    </div>
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $row->is_active ?? true))>
                    Active
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.pricing.margins.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
