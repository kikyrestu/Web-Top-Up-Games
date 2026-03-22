# 06. Upload Audit and Traceability

## Scope Upload
- Banner CMS
- Thumbnail artikel
- Bukti transfer manual (jika ada)
- Asset SEO image

## Pipeline Upload Aman
1. Client upload ke endpoint sementara.
2. Backend validasi mime, extension, ukuran, dimensi, dan checksum.
3. Simpan ke object storage dengan nama file acak.
4. Simpan metadata ke file_upload_logs.
5. Opsional scan antivirus (ClamAV service).
6. Hanya file verified yang dipublish ke CDN/public URL.

## Tabel Audit Upload
- file_upload_logs:
  - id
  - actor_type (ADMIN, USER, SYSTEM)
  - actor_id
  - original_name
  - storage_path
  - mime_type
  - file_size
  - sha256_checksum
  - upload_ip
  - user_agent
  - verdict (ACCEPTED, REJECTED, QUARANTINED)
  - reason
  - created_at

## Traceability Rules
- Semua perubahan file CMS wajib punya jejak actor.
- File lama tidak dihapus permanen langsung, gunakan soft-retention 30 hari.
- Setiap delete/replace file menghasilkan audit_logs event terpisah.

## Alerting
- Alert saat ada lonjakan upload gagal.
- Alert saat mime mismatch berulang dari IP yang sama.
- Alert saat terdeteksi pola upload script terselubung.
