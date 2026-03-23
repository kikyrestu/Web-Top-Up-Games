<x-layouts.app :title="'Admin Payment Management'">
    <div class="grid">
        <div class="panel">
            <h1>Payment Gateway Management</h1>
            <form method="get" action="{{ route('admin.payment.gateways.index') }}" class="grid" style="grid-template-columns:2fr 1fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Code / display name">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua</option>
                        <option value="ACTIVE" @selected($filters['status'] === 'ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected($filters['status'] === 'INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div><button class="btn" type="submit">Filter</button></div>
                <div><a class="pill" href="{{ route('admin.payment.gateways.create') }}">+ Tambah Gateway</a></div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Display Name</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Fee Flat</th>
                    <th>Fee %</th>
                    <th>Supported Methods</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->code }}</td>
                        <td>{{ $row->display_name }}</td>
                        <td>
                            @if ($row->is_active)
                                <span class="tag tag-pass">Active</span>
                            @else
                                <span class="tag tag-warn">Inactive</span>
                            @endif
                        </td>
                        <td>{{ (int) $row->priority }}</td>
                        <td>{{ number_format((float) $row->fee_flat, 2, ',', '.') }}</td>
                        <td>{{ number_format((float) $row->fee_percent, 2, ',', '.') }}</td>
                        <td>
                            @php
                                $methods = is_array($row->supported_methods) ? $row->supported_methods : [];
                            @endphp
                            {{ $methods !== [] ? implode(', ', $methods) : '-' }}
                        </td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.payment.gateways.edit', ['gateway' => $row->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.payment.gateways.destroy', ['gateway' => $row->id]) }}" onsubmit="return confirm('Hapus gateway setting ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted">Belum ada data payment gateway setting.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
