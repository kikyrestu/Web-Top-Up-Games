<x-layouts.app :title="'Akun - Ulasan Saya'">
    <div class="panel">
        <h1>Ulasan Saya</h1>
        <table>
            <thead>
            <tr><th>Produk</th><th>Rating</th><th>Status</th><th>Konten</th><th>Tanggal</th></tr>
            </thead>
            <tbody>
            @forelse ($reviews as $review)
                <tr>
                    <td>{{ $review->product?->name ?? '-' }}</td>
                    <td>{{ $review->rating }}/5</td>
                    <td>{{ $review->status }}</td>
                    <td>{{ $review->content }}</td>
                    <td>{{ $review->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Belum ada ulasan.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px;">{{ $reviews->links() }}</div>
    </div>
</x-layouts.app>
