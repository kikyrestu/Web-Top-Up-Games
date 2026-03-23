<x-layouts.app :title="'Admin Review Moderation'">
    @php
        $statsData = is_array($stats ?? null) ? $stats : [];
        $dailyStats = is_iterable($statsData['daily'] ?? null) ? collect($statsData['daily']) : collect();
    @endphp

    <div class="grid">
        <div class="panel">
            <h1>Moderation Stats</h1>
            <div class="cards" style="margin-top:12px;">
                <div class="card">
                    <div class="k">Pending Reviews</div>
                    <div class="v">{{ (int) ($statsData['pending_count'] ?? 0) }}</div>
                </div>
                <div class="card">
                    <div class="k">Today Approve</div>
                    <div class="v">{{ (int) ($statsData['today_approve_count'] ?? 0) }}</div>
                </div>
                <div class="card">
                    <div class="k">Today Reject</div>
                    <div class="v">{{ (int) ($statsData['today_reject_count'] ?? 0) }}</div>
                </div>
            </div>

            <table style="margin-top:12px;">
                <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Approve</th>
                    <th>Reject</th>
                    <th>Approve Rate</th>
                    <th>Reject Rate</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($dailyStats as $row)
                    <tr>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ (int) ($row['approve_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['reject_count'] ?? 0) }}</td>
                        <td>{{ (float) ($row['approve_rate_pct'] ?? 0) }}%</td>
                        <td>{{ (float) ($row['reject_rate_pct'] ?? 0) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada data moderasi harian.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h1>Review Moderation</h1>
            <form method="get" action="{{ route('admin.reviews.index') }}" class="grid" style="grid-template-columns:2fr 1fr auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Isi review / nama produk / order code">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        @foreach (['PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'ALL'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="btn" type="submit">Filter</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <form id="bulk-review-form" method="post" action="{{ route('admin.reviews.bulk-moderate') }}" class="grid" style="grid-template-columns:2fr 1fr auto; align-items:end; margin-bottom:12px;">
                @csrf
                <div>
                    <label for="bulk_reason">Catatan Bulk Moderation (opsional)</label>
                    <input id="bulk_reason" type="text" name="reason" placeholder="Alasan approve/reject massal">
                </div>
                <div>
                    <label for="bulk_action">Aksi</label>
                    <select id="bulk_action" name="action" required>
                        <option value="">Pilih aksi</option>
                        <option value="APPROVE">APPROVE</option>
                        <option value="REJECT">REJECT</option>
                    </select>
                </div>
                <div>
                    <button class="btn" type="submit">Apply ke yang dipilih</button>
                </div>
            </form>

            <table>
                <thead>
                <tr>
                    <th>
                        <input id="check-all-reviews" type="checkbox" aria-label="Select all reviews">
                    </th>
                    <th>Review</th>
                    <th>User</th>
                    <th>Produk</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Aksi Moderasi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td>
                            <input class="bulk-review-checkbox" type="checkbox" form="bulk-review-form" name="review_ids[]" value="{{ (int) $review->id }}" aria-label="Pilih review {{ (int) $review->id }}">
                        </td>
                        <td>
                            <strong>{{ (int) $review->rating }}/5</strong><br>
                            <span class="muted">{{ $review->content }}</span>
                        </td>
                        <td>
                            {{ $review->user?->name ?: 'Guest' }}<br>
                            <span class="muted">{{ $review->user?->email ?: '-' }}</span>
                        </td>
                        <td>{{ $review->product?->name ?: '-' }}</td>
                        <td>{{ $review->order?->order_code ?: '-' }}</td>
                        <td>
                            @if ($review->status === 'APPROVED')
                                <span class="tag tag-pass">APPROVED</span>
                            @elseif ($review->status === 'REJECTED')
                                <span class="tag tag-fail">REJECTED</span>
                            @else
                                <span class="tag tag-warn">PENDING</span>
                            @endif
                        </td>
                        <td>
                            <div class="grid" style="grid-template-columns:1fr; gap:8px; min-width:230px;">
                                <a class="pill" href="{{ route('admin.reviews.show', ['review' => $review->id]) }}">Detail & History</a>
                                <form method="post" action="{{ route('admin.reviews.approve', ['review' => $review->id]) }}" class="grid" style="gap:6px;">
                                    @csrf
                                    <input type="text" name="reason" placeholder="Catatan approve (opsional)">
                                    <button class="btn" type="submit">Approve</button>
                                </form>
                                <form method="post" action="{{ route('admin.reviews.reject', ['review' => $review->id]) }}" class="grid" style="gap:6px;">
                                    @csrf
                                    <input type="text" name="reason" placeholder="Alasan reject (opsional)">
                                    <button class="btn btn-ghost" type="submit">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Belum ada review untuk dimoderasi.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">
                {{ $reviews->links() }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkAll = document.getElementById('check-all-reviews');
            const rowChecks = Array.from(document.querySelectorAll('.bulk-review-checkbox'));

            if (!checkAll || rowChecks.length === 0) {
                return;
            }

            checkAll.addEventListener('change', function () {
                rowChecks.forEach(function (checkbox) {
                    checkbox.checked = checkAll.checked;
                });
            });

            rowChecks.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    const selected = rowChecks.filter(function (node) {
                        return node.checked;
                    }).length;
                    checkAll.checked = selected === rowChecks.length;
                    checkAll.indeterminate = selected > 0 && selected < rowChecks.length;
                });
            });
        });
    </script>
</x-layouts.app>
