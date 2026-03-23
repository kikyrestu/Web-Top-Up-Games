<x-layouts.app :title="($formMode === 'create' ? 'Tambah Mapping Nominal' : 'Edit Mapping Nominal')">
    <div class="grid" style="max-width: 920px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah Mapping Nominal' : 'Edit Mapping Nominal' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.nominal.mappings.store') : route('admin.nominal.mappings.update', ['mapping' => $row->id]) }}" class="grid" style="margin-top:12px;">
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

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="provider_product_code">Provider Product Code</label>
                        <input id="provider_product_code" name="provider_product_code" type="text" value="{{ old('provider_product_code', $row->provider_product_code) }}" required>
                    </div>
                    <div>
                        <label for="provider_product_name">Provider Product Name</label>
                        <input id="provider_product_name" name="provider_product_name" type="text" value="{{ old('provider_product_name', $row->provider_product_name) }}" required>
                    </div>
                </div>

                <div>
                    <label for="raw_payload_json">Raw Payload JSON (opsional)</label>
                    <textarea id="raw_payload_json" name="raw_payload_json" rows="10" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;">{{ old('raw_payload_json', !empty($row->raw_payload) ? json_encode($row->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="is_available" value="1" @checked((bool) old('is_available', $row->is_available ?? true))>
                    Is Available
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.nominal.mappings.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
