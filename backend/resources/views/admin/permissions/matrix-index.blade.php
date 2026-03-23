<x-layouts.app :title="'Admin Permission Matrix'">
    <div class="grid">
        <div class="panel">
            <h1>Granular Permission Matrix</h1>
            <p class="muted">Atur scope menu dan aksi per role admin panel.</p>
        </div>

        <div class="panel" style="overflow:auto;">
            <form method="post" action="{{ route('admin.permissions.matrix.update') }}" class="grid">
                @csrf
                @method('put')

                <table>
                    <thead>
                    <tr>
                        <th>Permission Key</th>
                        @foreach ($roles as $role)
                            <th>{{ strtoupper($role) }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($keys as $key)
                        <tr>
                            <td><strong>{{ $key }}</strong></td>
                            @foreach ($roles as $role)
                                <td>
                                    <label style="display:flex; align-items:center; gap:8px;">
                                        <input type="hidden" name="matrix[{{ $role }}][{{ $key }}]" value="0">
                                        <input type="checkbox" name="matrix[{{ $role }}][{{ $key }}]" value="1" @checked((bool) ($matrix[$role][$key] ?? false))>
                                        Allow
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div style="display:flex; gap:10px; margin-top:12px;">
                    <button class="btn" type="submit">Simpan Matrix</button>
                    <a class="pill" href="{{ route('admin.dashboard') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
