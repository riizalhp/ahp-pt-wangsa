# Kredensial Akses Pengguna - SPK Pemilihan Supplier (AHP)

Berikut adalah daftar akun default yang dapat digunakan untuk masuk ke dalam Sistem Pendukung Keputusan (SPK) Pemilihan Supplier PT Wangsa Jatra Lestari:

## Daftar Akun

| Peran (Role) | Username | Password | Deskripsi Tugas / Akses Fitur |
| :--- | :--- | :--- | :--- |
| **Supervisor Procurement** | `spv1` | `password` | Mengelola data master (Supplier, Produk, Kriteria, Subkriteria), melakukan input perbandingan berpasangan (AHP) untuk menentukan peringkat, dan menyetujui/mengajukan laporan penilaian. |
| **Sales Marketing** | `sales1` | `password` | Menginput data Purchase Order (PO) baru untuk pengadaan barang ke supplier beserta dokumentasi foto, dan melihat laporan secara read-only. |
| **Staff Logistik** | `log1` | `password` | Mencatat kedatangan barang aktual (tanggal kedatangan, jumlah diterima, jumlah cacat) untuk menghitung persentase kualitas & keterlambatan secara otomatis, dan melihat laporan secara read-only. |

---

## Cara Penggunaan
1. Pastikan server lokal aktif dengan menjalankan perintah `php artisan serve`.
2. Buka browser dan arahkan ke alamat **[http://127.0.0.1:8000/login](http://127.0.0.1:8000/login)**.
3. Masukkan salah satu **Username** dan **Password** di atas sesuai dengan modul yang ingin Anda uji/akses.
4. Tekan tombol **Masuk ke Akun**.
