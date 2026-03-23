<x-layouts.app :title="'Akun - Profil'">
    <div class="panel" style="max-width:760px; margin:0 auto;">
        <h1>Profil Saya</h1>
        <form method="post" action="{{ route('account.profile.update') }}" class="grid" style="margin-top:12px;">
            @csrf
            <div>
                <label for="name">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div style="display:flex; justify-content:flex-end;"><button class="btn" type="submit">Simpan Profil</button></div>
        </form>
    </div>
</x-layouts.app>
