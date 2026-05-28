# 🖥️ SPK Pemilihan Supplier (AHP) - PT Wangsa Jatra Lestari
> **Sistem Pendukung Keputusan (SPK) Pemilihan Supplier menggunakan Metode Analytic Hierarchy Process (AHP).**

Panduan ini dibuat khusus untuk memudahkan proses instalasi aplikasi di perangkat lokal (laptop/komputer Anda). Panduan ini ditulis dengan bahasa yang mudah dipahami, bahkan bagi Anda yang tidak memiliki latar belakang di bidang Teknologi Informasi (IT).

---

## 📋 Daftar Isi
1. [Prasyarat (Aplikasi yang Harus Diinstal)](#1-prasyarat-aplikasi-yang-harus-diinstal)
2. [Langkah-Langkah Instalasi Aplikasi](#2-langkah-langkah-instalasi-aplikasi)
3. [Cara Menjalankan Aplikasi](#3-cara-menjalankan-aplikasi)
4. [Kredensial Akun Default (Untuk Login)](#4-kredensial-akun-default-untuk-login)
5. [Penyelesaian Masalah (Troubleshooting)](#5-penyelesaian-masalah-troubleshooting)

---

## 1. Prasyarat (Aplikasi yang Harus Diinstal)

Sebelum memulai instalasi website, Anda perlu mengunduh dan menginstal beberapa software pendukung berikut ini. Silakan unduh melalui tautan resmi di bawah:

### A. XAMPP (Versi PHP 8.2 ke atas)
* **Kegunaan**: Menyediakan mesin PHP untuk menjalankan website dan mengelola sistem di komputer Anda.
* **Link Unduh**: [Unduh XAMPP Resmi](https://www.apachefriends.org/download.html)
* **Catatan**: Pastikan Anda memilih versi **PHP 8.2.x** atau **PHP 8.3.x** saat mengunduh. Jangan unduh versi yang di bawahnya karena website tidak akan berjalan.

### B. Composer (Pengelola Bahan Website)
* **Kegunaan**: Mengunduh dan memasang modul pendukung website secara otomatis.
* **Link Unduh**: [Unduh Composer Resmi](https://getcomposer.org/Composer-Setup.exe)
* **Catatan**: Unduh file `.exe` tersebut, lalu instal seperti biasa di komputer Anda.

### C. Git (Opsional)
* **Kegunaan**: Untuk mengambil kode website langsung dari internet. Jika tidak ingin menginstal Git, Anda bisa langsung mengunduh file ZIP website ini dari GitHub.
* **Link Unduh**: [Unduh Git Resmi](https://git-scm.com/downloads)

---

## 2. Langkah-Langkah Instalasi Aplikasi

Ikuti langkah-langkah di bawah ini secara berurutan. Jangan ada langkah yang dilewati.

### Langkah 1: Mengunduh Source Code Website
1. Buka halaman GitHub website ini di browser Anda.
2. Klik tombol hijau bertuliskan **Code**.
3. Pilih **Download ZIP**.
4. Setelah terunduh, ekstrak file ZIP tersebut.
5. Pindahkan folder hasil ekstrak ke dalam direktori XAMPP Anda, biasanya di:
   `C:\xampp\htdocs\pt-wangsa-ahp`
   *(Pastikan nama foldernya adalah `pt-wangsa-ahp` agar seragam)*.

### Langkah 2: Daftarkan PHP XAMPP ke Sistem Windows (PENTING!)
Agar perintah PHP dapat dikenali di komputer Anda, ikuti panduan berikut:
1. Buka menu pencarian Windows (tekan tombol Windows di keyboard), lalu ketik **Environment Variables**.
2. Pilih menu **Edit the system environment variables**.
3. Sebuah jendela kecil akan muncul. Klik tombol **Environment Variables...** di bagian bawah.
4. Pada kolom **System variables** (kotak bagian bawah), cari baris bernama **Path**, lalu klik baris tersebut dan klik tombol **Edit...**.
5. Di jendela baru yang muncul, klik tombol **New** di sebelah kanan.
6. Ketik/masukkan alamat folder PHP XAMPP Anda. Secara bawaan alamatnya adalah:
   `C:\xampp\php`
7. Klik **OK** di semua jendela yang terbuka untuk menyimpan perubahan.
8. Untuk memastikan ini berhasil: Buka aplikasi **Command Prompt** (ketik `cmd` di pencarian Windows), ketik perintah `php -v`, lalu tekan Enter. Jika muncul teks bertuliskan `PHP 8.2.x` atau versi di atasnya, berarti langkah ini berhasil!

### Langkah 3: Menginstal Composer
1. Buka file installer Composer (`Composer-Setup.exe`) yang sudah diunduh sebelumnya.
2. Pilih **Install for all users** (direkomendasikan).
3. Klik **Next**.
4. Di bagian pemilihan lokasi PHP (*Choose the command-line PHP*), pastikan sudah terarah ke `C:\xampp\php\php.exe`. Jika belum, klik **Browse** dan cari file `php.exe` di dalam folder `C:\xampp\php`.
5. Klik **Next** terus menerus hingga proses instalasi selesai.

### Langkah 4: Menyiapkan File Pengaturan (.env)
1. Buka folder proyek website Anda di `C:\xampp\htdocs\pt-wangsa-ahp`.
2. Cari file bernama `.env.example`.
3. Klik kanan file tersebut, lalu pilih **Copy** (Salin), kemudian **Paste** (Tempel) di folder yang sama.
4. Klik kanan file salinan tersebut, lalu ubah namanya (Rename) menjadi `.env` (pastikan tanda titik di depan tetap ada dan hapus tulisan `.example`).
5. *Catatan*: Website ini menggunakan database internal bertipe **SQLite** (file `database.sqlite` di dalam folder `database`). Anda tidak perlu melakukan setting database di phpMyAdmin XAMPP karena database sudah terkonfigurasi otomatis dan siap digunakan.

### Langkah 5: Menginstal Komponen Website
1. Buka aplikasi **Command Prompt** (cmd) di Windows Anda.
2. Masuk ke folder website Anda dengan mengetik perintah berikut dan tekan Enter:
   ```bash
   cd C:\xampp\htdocs\pt-wangsa-ahp
   ```
3. Jalankan perintah berikut untuk mendownload bahan-bahan website:
   ```bash
   composer install
   ```
   *Tunggu prosesnya sampai selesai. Proses ini memerlukan koneksi internet stabil.*

### Langkah 6: Membuat Kunci Keamanan & Menyiapkan Database
Tetap di Command Prompt Anda pada folder website, lalu jalankan dua perintah berikut satu per satu:

1. **Membuat Kunci Aplikasi:**
   ```bash
   php artisan key:generate
   ```
2. **Membuat Tabel dan Akun Uji Coba:**
   ```bash
   php artisan migrate --seed
   ```
   *Jika muncul pertanyaan: `"Database does not exist. Do you want to create it? (yes/no)"`, ketik `yes` lalu tekan Enter.*

---

## 3. Cara Menjalankan Aplikasi

Setiap kali Anda ingin membuka website ini di komputer Anda, ikuti langkah mudah berikut:

1. Buka **Command Prompt** (cmd).
2. Masuk ke folder website Anda:
   ```bash
   cd C:\xampp\htdocs\pt-wangsa-ahp
   ```
3. Jalankan server lokal dengan perintah:
   ```bash
   php artisan serve
   ```
4. Setelah muncul teks `Server running on [http://127.0.0.1:8000]`, buka browser internet Anda (seperti Google Chrome atau Microsoft Edge).
5. Ketik alamat berikut di bagian atas browser Anda:
   **[http://127.0.0.1:8000](http://127.0.0.1:8000)** atau **[http://localhost:8000](http://localhost:8000)**
6. Tekan Enter. Halaman website SPK akan tampil dan siap digunakan!
7. *Penting*: Jangan menutup Command Prompt selama Anda membuka website tersebut. Jika ingin mematikan website, tekan tombol `Ctrl + C` secara bersamaan di Command Prompt.

---

## 4. Kredensial Akun Default (Untuk Login)

Berikut adalah daftar akun default yang sudah otomatis terdaftar di database untuk keperluan uji coba website SPK ini:

| Peran (Role) | Username | Password | Deskripsi Tugas / Akses Fitur |
| :--- | :--- | :--- | :--- |
| **Supervisor Procurement** | `spv1` | `password` | Mengelola data kriteria & alternatif, input penilaian AHP, dan menyetujui hasil rekomendasi supplier terbaik. |
| **Sales Marketing** | `sales1` | `password` | Menginput data Purchase Order (PO) baru dan melihat laporan. |
| **Staff Logistik** | `log1` | `password` | Mencatat kedatangan barang aktual (kualitas & keterlambatan pengiriman) serta melihat laporan. |

---

## 5. Penyelesaian Masalah (Troubleshooting)

Berikut adalah solusi jika Anda menemui kendala saat instalasi:

* **Kendala: Perintah `php` atau `composer` tidak dikenal (*not recognized*)**
  * *Solusi*: Anda belum mendaftarkan PHP XAMPP ke Environment Variables Windows (Langkah 2 di atas). Pastikan Anda sudah mengikuti panduan tersebut dengan benar, lalu **tutup dan buka kembali** jendela Command Prompt Anda agar sistem memperbarui pengaturannya.
* **Kendala: Muncul error database ketika menjalankan perintah migrate**
  * *Solusi*: Pastikan file `.env` sudah dibuat dengan benar dari salinan `.env.example`. Di dalam file `.env`, pastikan baris `DB_CONNECTION=sqlite` aktif dan tidak ada tanda pagar (`#`) di depannya.
* **Kendala: Halaman website tampak berantakan atau tidak rapi**
  * *Solusi*: Tampilan web ini telah dikompilasi sebelumnya. Jika ingin memperbarui tampilan asetnya secara manual (memerlukan Node.js), Anda bisa menjalankan perintah `npm install` lalu dilanjutkan dengan `npm run build` di folder proyek Anda.
