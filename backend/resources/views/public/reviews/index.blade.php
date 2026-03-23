<x-layouts.app :title="'Ulasan Pelanggan'">
    <div class="grid">
        <div class="panel">
            <h1>Ulasan Pelanggan</h1>
            <div class="cards" style="margin-top:10px;">
                <div class="card"><div class="k">Total Ulasan</div><div class="v">{{ (int) $ratingStats['total'] }}</div></div>
                <div class="card"><div class="k">Rata-Rata Rating</div><div class="v">{{ number_format((float) $ratingStats['avg'], 2) }}</div></div>
                <div class="card"><div class="k">Bintang 5</div><div class="v">{{ (int) $ratingStats['five_star'] }}</div></div>
            </div>
        </div>

        <div class="panel">
            @forelse ($reviews as $review)
                <div style="border-bottom:1px solid var(--line); padding:10px 0;">
                    <div><strong>{{ $review->user?->name ?? 'Guest' }}</strong> • {{ $review->product?->name ?? '-' }}</div>
                    <div class="muted">Rating {{ $review->rating }}/5 • {{ $review->approved_at ?: $review->created_at }}</div>
                    <p style="margin-top:6px;">{{ $review->content }}</p>
                </div>
            @empty
                <p class="muted">Belum ada ulasan disetujui.</p>
            @endforelse

            <div style="margin-top:12px;">
                {{ $reviews->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
