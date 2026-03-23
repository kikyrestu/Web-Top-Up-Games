<x-layouts.app :title="($formMode === 'create' ? 'Tambah Payment Gateway' : 'Edit Payment Gateway')">
    <div class="grid" style="max-width: 920px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah Payment Gateway' : 'Edit Payment Gateway' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.payment.gateways.store') : route('admin.payment.gateways.update', ['gateway' => $row->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="code">Code</label>
                        <select id="code" name="code" required>
                            <option value="">Pilih gateway</option>
                            @foreach ($availableCodes as $code)
                                <option value="{{ $code }}" @selected(old('code', $row->code) === $code)>{{ $code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="display_name">Display Name</label>
                        <input id="display_name" name="display_name" type="text" value="{{ old('display_name', $row->display_name) }}" required>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr 1fr;">
                    <div>
                        <label for="priority">Priority (angka kecil lebih diprioritaskan)</label>
                        <input id="priority" name="priority" type="number" min="1" max="9999" value="{{ old('priority', $row->priority ?: 100) }}" required>
                    </div>
                    <div>
                        <label for="fee_flat">Fee Flat</label>
                        <input id="fee_flat" name="fee_flat" type="number" min="0" step="0.01" value="{{ old('fee_flat', $row->fee_flat ?? 0) }}">
                    </div>
                    <div>
                        <label for="fee_percent">Fee Percent</label>
                        <input id="fee_percent" name="fee_percent" type="number" min="0" max="100" step="0.01" value="{{ old('fee_percent', $row->fee_percent ?? 0) }}">
                    </div>
                </div>

                <div>
                    <label for="supported_methods_text">Supported Methods (satu baris satu metode, opsional)</label>
                    <textarea id="supported_methods_text" name="supported_methods_text" rows="8" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;">{{ old('supported_methods_text', is_array($row->supported_methods ?? null) ? implode(PHP_EOL, $row->supported_methods) : '') }}</textarea>
                </div>

                <div>
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="4" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;">{{ old('notes', $row->notes) }}</textarea>
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $row->is_active ?? true))>
                    Active
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.payment.gateways.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
