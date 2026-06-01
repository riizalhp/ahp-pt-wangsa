# 🖥️ SPK Pemilihan Supplier (AHP) - PT Wangsa Jatra Lestari
> **Sistem Pendukung Keputusan (SPK) Pemilihan Supplier menggunakan Metode Analytic Hierarchy Process (AHP).**

Panduan ini dibuat khusus untuk memudahkan proses instalasi aplikasi di perangkat lokal (laptop/komputer Anda). Panduan ini ditulis dengan bahasa yang mudah dipahami, bahkan bagi Anda yang tidak memiliki latar belakang di bidang Teknologi Informasi (IT).

---

## 📋 Daftar Isi
1. [Prasyarat (Aplikasi yang Harus Diinstal)](#1-prasyarat-aplikasi-yang-harus-diinstal)
2. [Langkah-Langkah Instalasi Aplikasi](#2-langkah-langkah-instalasi-aplikasi)
3. [Cara Menjalankan Aplikasi](#3-cara-menjalankan-aplikasi)
4. [Memperbarui Aplikasi (Menarik Versi Terbaru)](#4-memperbarui-aplikasi-menarik-versi-terbaru)
5. [Kredensial Akun Default (Untuk Login)](#5-kredensial-akun-default-untuk-login)
6. [Penyelesaian Masalah (Troubleshooting)](#6-penyelesaian-masalah-troubleshooting)

---

## 1. Prasyarat (Aplikasi yang Harus Diinstal)

Sebelum memulai instalasi website, Anda perlu mengunduh dan menginstal beberapa software pendukung berikut ini. Silakan unduh melalui tautan resmi di bawah:

### A. XAMPP (Versi PHP 8.2 ke atas)
* **Kegunaan**: Menyediakan mesin PHP untuk menjalankan website serta database **MySQL** (lewat modul MySQL/phpMyAdmin) untuk menyimpan data aplikasi.
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
5. Buka file `.env` tersebut menggunakan Notepad (atau editor teks lainnya), lalu pastikan bagian pengaturan database berisi seperti berikut ini:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=spk_supplier
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *Catatan: Secara bawaan XAMPP, username database adalah `root` dan password-nya kosong (dibiarkan tanpa diisi). Simpan file `.env` setelah selesai.*

### Langkah 5: Membuat Database di phpMyAdmin
Website ini menggunakan database **MySQL**, jadi Anda perlu menyiapkan database kosong terlebih dahulu:
1. Buka aplikasi **XAMPP Control Panel**, lalu klik tombol **Start** pada modul **Apache** dan **MySQL** hingga keduanya berwarna hijau.
2. Buka browser, lalu akses alamat **[http://localhost/phpmyadmin](http://localhost/phpmyadmin)**.
3. Pada menu sebelah kiri, klik tombol **New** (Baru).
4. Pada kolom **Database name**, ketik nama database: `spk_supplier` (harus sama persis dengan yang ada di file `.env`).
5. Klik tombol **Create** (Buat). Database kosong akan terbuat. Anda **tidak perlu** membuat tabel secara manual karena tabel akan dibuat otomatis pada langkah berikutnya.

### Langkah 6: Menginstal Komponen Website
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

### Langkah 7: Membuat Kunci Keamanan & Menyiapkan Database
Sebelum menjalankan perintah ini, pastikan modul **MySQL** di XAMPP Control Panel sudah aktif (hijau) dan database `spk_supplier` sudah dibuat (Langkah 5).

Tetap di Command Prompt Anda pada folder website, lalu jalankan dua perintah berikut satu per satu:

1. **Membuat Kunci Aplikasi:**
   ```bash
   php artisan key:generate
   ```
2. **Membuat Tabel dan Akun Uji Coba:**
   ```bash
   php artisan migrate --seed
   ```
   *Perintah ini akan otomatis membuat seluruh tabel di dalam database `spk_supplier` dan mengisi akun default. Anda tidak perlu membuat tabel secara manual di phpMyAdmin.*

---

## 3. Cara Menjalankan Aplikasi

Setiap kali Anda ingin membuka website ini di komputer Anda, ikuti langkah mudah berikut:

1. Buka **XAMPP Control Panel**, lalu klik **Start** pada modul **Apache** dan **MySQL** (harus berwarna hijau). Langkah ini wajib karena data website tersimpan di database MySQL.
2. Buka **Command Prompt** (cmd).
3. Masuk ke folder website Anda:
   ```bash
   cd C:\xampp\htdocs\pt-wangsa-ahp
   ```
4. Jalankan server lokal dengan perintah:
   ```bash
   php artisan serve
   ```
5. Setelah muncul teks `Server running on [http://127.0.0.1:8000]`, buka browser internet Anda (seperti Google Chrome atau Microsoft Edge).
6. Ketik alamat berikut di bagian atas browser Anda:
   **[http://127.0.0.1:8000](http://127.0.0.1:8000)** atau **[http://localhost:8000](http://localhost:8000)**
7. Tekan Enter. Halaman website SPK akan tampil dan siap digunakan!
8. *Penting*: Jangan menutup Command Prompt selama Anda membuka website tersebut. Jika ingin mematikan website, tekan tombol `Ctrl + C` secara bersamaan di Command Prompt.

---

## 4. Memperbarui Aplikasi (Menarik Versi Terbaru)

Bagian ini ditujukan untuk Anda yang **sudah pernah meng-clone / mengunduh** website ini sebelumnya dan ingin mengambil pembaruan terbaru (versi yang sudah dipindah ke database **MySQL**). Ikuti langkah berikut sesuai cara Anda mengunduh website pertama kali.

> ⚠️ **Penting:** Mulai versi ini, website **tidak lagi menggunakan SQLite**, melainkan **MySQL**. Jadi pastikan modul **MySQL** di XAMPP sudah aktif dan database `spk_supplier` sudah dibuat (lihat [Langkah 5](#langkah-5-membuat-database-di-phpmyadmin) pada bagian instalasi) sebelum menjalankan langkah di bawah.

### A. Jika Anda Mengunduh Lewat Git (clone)

1. Buka **XAMPP Control Panel**, lalu **Start** modul **Apache** dan **MySQL** hingga berwarna hijau.
2. Buka **Command Prompt** (cmd) dan masuk ke folder website:
   ```bash
   cd C:\xampp\htdocs\pt-wangsa-ahp
   ```
3. Tarik versi terbaru dari GitHub:
   ```bash
   git pull
   ```
   *Jika muncul pesan konflik karena Anda pernah mengubah file (misalnya `.env`), aman saja — file `.env` Anda tidak akan tertimpa karena tidak ikut dilacak Git.*
4. Perbarui komponen website (jika ada modul baru yang ditambahkan):
   ```bash
   composer install
   ```
5. Pastikan pengaturan database di file `.env` Anda sudah memakai MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=spk_supplier
   DB_USERNAME=root
   DB_PASSWORD=
   ```
6. Bersihkan cache pengaturan lama agar koneksi MySQL terbaca:
   ```bash
   php artisan config:clear
   ```
7. Terapkan perubahan struktur tabel terbaru (jika ada migrasi baru):
   ```bash
   php artisan migrate
   ```
   *Perintah ini hanya menjalankan tabel yang belum ada. Data Anda yang sudah tersimpan di MySQL tidak akan terhapus.*

### B. Jika Anda Mengunduh Lewat File ZIP (Download ZIP)

Karena versi ZIP tidak terhubung ke Git, Anda perlu mengunduh ulang lalu menyalin kembali pengaturan Anda:

1. Unduh ulang file ZIP terbaru dari GitHub (tombol **Code** → **Download ZIP**), lalu ekstrak.
2. **Simpan dulu** file `.env` lama Anda dari folder website yang lama (salin ke tempat aman). File ini berisi pengaturan database Anda.
3. Ganti folder website lama dengan hasil ekstrak yang baru di `C:\xampp\htdocs\pt-wangsa-ahp`.
4. Kembalikan file `.env` lama Anda ke dalam folder website yang baru (timpa `.env` bawaan bila ada).
5. Buka Command Prompt di folder website, lalu jalankan:
   ```bash
   composer install
   php artisan config:clear
   php artisan migrate
   ```

### ⚠️ Catatan Penting Saat Memperbarui

* **Jangan menjalankan `php artisan migrate:fresh`** kecuali Anda benar-benar ingin **menghapus seluruh data** dan memulai dari awal. Perintah ini akan mengosongkan semua tabel beserta isinya.
* Jika ingin sekaligus mengisi ulang akun default (hanya saat database masih kosong), gunakan `php artisan migrate --seed`.
* Selalu pastikan **MySQL aktif** di XAMPP sebelum menjalankan perintah `artisan` apa pun, agar tidak muncul error koneksi database.

---

## 5. Kredensial Akun Default (Untuk Login)

Berikut adalah daftar akun default yang sudah otomatis terdaftar di database untuk keperluan uji coba website SPK ini:

| Peran (Role) | Username | Password | Deskripsi Tugas / Akses Fitur |
| :--- | :--- | :--- | :--- |
| **Supervisor Procurement** | `spv1` | `password` | Mengelola data kriteria & alternatif, input penilaian AHP, dan menyetujui hasil rekomendasi supplier terbaik. |
| **Sales Marketing** | `sales1` | `password` | Menginput data Purchase Order (PO) baru dan melihat laporan. |
| **Staff Logistik** | `log1` | `password` | Mencatat kedatangan barang aktual (kualitas & keterlambatan pengiriman) serta melihat laporan. |

---

## 6. Penyelesaian Masalah (Troubleshooting)

Berikut adalah solusi jika Anda menemui kendala saat instalasi:

* **Kendala: Perintah `php` atau `composer` tidak dikenal (*not recognized*)**
  * *Solusi*: Anda belum mendaftarkan PHP XAMPP ke Environment Variables Windows (Langkah 2 di atas). Pastikan Anda sudah mengikuti panduan tersebut dengan benar, lalu **tutup dan buka kembali** jendela Command Prompt Anda agar sistem memperbarui pengaturannya.
* **Kendala: Muncul error database ketika menjalankan perintah migrate**
  * *Solusi*: Pastikan modul **MySQL** di XAMPP Control Panel sudah aktif (berwarna hijau) dan database `spk_supplier` sudah Anda buat di phpMyAdmin (Langkah 5). Selain itu, periksa kembali file `.env` Anda: pastikan baris `DB_CONNECTION=mysql`, `DB_DATABASE=spk_supplier`, `DB_USERNAME=root`, dan `DB_PASSWORD=` (kosong) sudah sesuai.
* **Kendala: Muncul pesan error `SQLSTATE[HY000] [2002]` atau `Connection refused`**
  * *Solusi*: Pesan ini muncul karena MySQL belum berjalan. Buka **XAMPP Control Panel**, lalu klik **Start** pada modul **MySQL** hingga berwarna hijau, kemudian coba jalankan lagi perintahnya.
* **Kendala: Muncul pesan error `Unknown database 'spk_supplier'`**
  * *Solusi*: Database belum dibuat. Ikuti kembali Langkah 5 untuk membuat database kosong bernama `spk_supplier` di phpMyAdmin.
* **Kendala: Muncul pesan error `1273 Unknown collation: 'utf8mb4_0900_ai_ci'`**
  * *Solusi*: Error ini muncul jika database Anda menggunakan **MariaDB** (bawaan beberapa versi XAMPP), karena collation `utf8mb4_0900_ai_ci` hanya dikenal oleh MySQL 8. Pastikan file `.env` Anda memiliki dua baris berikut, lalu jalankan `php artisan config:clear`:
    ```env
    DB_CHARSET=utf8mb4
    DB_COLLATION=utf8mb4_unicode_ci
    ```
    Jika Anda baru saja menyalin `.env` dari `.env.example` versi terbaru, baris ini sudah otomatis ada.
* **Kendala: Halaman website tampak berantakan atau tidak rapi**
  * *Solusi*: Tampilan web ini telah dikompilasi sebelumnya. Jika ingin memperbarui tampilan asetnya secara manual (memerlukan Node.js), Anda bisa menjalankan perintah `npm install` lalu dilanjutkan dengan `npm run build` di folder proyek Anda.
