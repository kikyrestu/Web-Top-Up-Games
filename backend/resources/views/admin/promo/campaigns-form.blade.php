<x-layouts.app :title="($formMode === 'create' ? 'Tambah Promo Campaign' : 'Edit Promo Campaign')">
    <div class="grid" style="max-width: 920px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah Promo Campaign' : 'Edit Promo Campaign' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.promo.campaigns.store') : route('admin.promo.campaigns.update', ['campaign' => $row->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                @php
                    $scope = old('scope', strtoupper((string) ($row->scope ?: 'GLOBAL')));
                    $type = old('campaign_type', strtoupper((string) ($row->campaign_type ?: 'VOUCHER')));
                    $mode = old('discount_mode', strtoupper((string) ($row->discount_mode ?: 'FLAT')));
                @endphp

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="name">Nama Campaign</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $row->name) }}" required>
                    </div>
                    <div>
                        <label for="code">Code Promo</label>
                        <input id="code" name="code" type="text" value="{{ old('code', $row->code) }}" required>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr 1fr;">
                    <div>
                        <label for="campaign_type">Campaign Type</label>
                        <select id="campaign_type" name="campaign_type" required>
                            <option value="VOUCHER" @selected($type === 'VOUCHER')>VOUCHER</option>
                            <option value="CASHBACK" @selected($type === 'CASHBACK')>CASHBACK</option>
                        </select>
                    </div>
                    <div>
                        <label for="discount_mode">Reward Mode</label>
                        <select id="discount_mode" name="discount_mode" required>
                            <option value="FLAT" @selected($mode === 'FLAT')>FLAT</option>
                            <option value="PERCENTAGE" @selected($mode === 'PERCENTAGE')>PERCENTAGE</option>
                        </select>
                    </div>
                    <div>
                        <label for="discount_value">Reward Value</label>
                        <input id="discount_value" name="discount_value" type="number" min="0" step="0.01" value="{{ old('discount_value', $row->discount_value ?? 0) }}" required>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr 1fr;">
                    <div>
                        <label for="min_order_amount">Minimum Order</label>
                        <input id="min_order_amount" name="min_order_amount" type="number" min="0" step="0.01" value="{{ old('min_order_amount', $row->min_order_amount ?? 0) }}">
                    </div>
                    <div>
                        <label for="max_discount_amount">Maksimum Reward</label>
                        <input id="max_discount_amount" name="max_discount_amount" type="number" min="0" step="0.01" value="{{ old('max_discount_amount', $row->max_discount_amount) }}">
                    </div>
                    <div>
                        <label for="scope">Scope</label>
                        <select id="scope" name="scope" required>
                            <option value="GLOBAL" @selected($scope === 'GLOBAL')>GLOBAL</option>
                            <option value="CATEGORY" @selected($scope === 'CATEGORY')>CATEGORY</option>
                            <option value="PRODUCT" @selected($scope === 'PRODUCT')>PRODUCT</option>
                        </select>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="category_id">Category (scope CATEGORY)</label>
                        <select id="category_id" name="category_id">
                            <option value="">-</option>
                            @foreach ($categories as $category)
                                <option value="{{ (int) $category->id }}" @selected((int) old('category_id', $row->category_id) === (int) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="product_id">Product (scope PRODUCT)</label>
                        <select id="product_id" name="product_id">
                            <option value="">-</option>
                            @foreach ($products as $product)
                                <option value="{{ (int) $product->id }}" @selected((int) old('product_id', $row->product_id) === (int) $product->id)>{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="quota_total">Kuota Total</label>
                        <input id="quota_total" name="quota_total" type="number" min="1" value="{{ old('quota_total', $row->quota_total) }}" placeholder="Kosong = unlimited">
                    </div>
                    <div>
                        <label for="quota_per_user">Kuota per User</label>
                        <input id="quota_per_user" name="quota_per_user" type="number" min="1" value="{{ old('quota_per_user', $row->quota_per_user) }}" placeholder="Kosong = unlimited">
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="start_at">Mulai</label>
                        <input id="start_at" name="start_at" type="datetime-local" value="{{ old('start_at', $row->start_at ? $row->start_at->format('Y-m-d\TH:i') : null) }}">
                    </div>
                    <div>
                        <label for="end_at">Berakhir</label>
                        <input id="end_at" name="end_at" type="datetime-local" value="{{ old('end_at', $row->end_at ? $row->end_at->format('Y-m-d\TH:i') : null) }}">
                    </div>
                </div>

                <div>
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;">{{ old('description', $row->description) }}</textarea>
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $row->is_active ?? true))>
                    Active
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.promo.campaigns.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
