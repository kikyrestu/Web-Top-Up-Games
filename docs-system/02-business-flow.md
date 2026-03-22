# 02. Business Flow

## A. Game Top-Up Flow
1. User pilih game dan nominal.
2. Sistem validasi user ID/game server.
3. Engine tarik kandidat harga dari 3 provider.
4. Engine pilih provider termurah (active and healthy).
5. User checkout, pilih metode bayar.
6. Setelah webhook pembayaran sukses, order dikirim ke provider terpilih.
7. Jika provider gagal, failover ke provider berikutnya sesuai ranking.
8. Status order diperbarui ke SUCCESS/FAILED.

## B. PPOB Multifinance Flow
1. User input data tagihan (nomor pelanggan, periode, dll).
2. Sistem request inquiry ke provider.
3. Engine bandingkan 3 provider dengan rule:
   - Prioritas 1: admin fee Rp 0.
   - Prioritas 2: jika admin sama, pilih komisi tertinggi.
4. User konfirmasi dan bayar.
5. Webhook sukses memicu proses payment posting ke provider.
6. Respon provider dicatat dan diteruskan ke user.

## C. Guest to User Sync Flow
1. Guest transaksi menggunakan nomor WA/email.
2. Sistem simpan fingerprint perangkat + guest identity key.
3. Saat login OTP, sistem cek kemiripan identity.
4. Riwayat transaksi guest di-link ke account user.

## D. Testimonial Flow
1. User hanya bisa review jika ada transaksi SUCCESS terkait produk.
2. Untuk transaksi guest, review tetap valid jika identity cocok.
3. Semua review masuk status PENDING_APPROVAL.
4. Admin approve/reject dari panel.
