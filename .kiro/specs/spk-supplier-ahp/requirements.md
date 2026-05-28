# Requirements Document

## Introduction

Sistem Pendukung Keputusan (SPK) Pemilihan Supplier ini dibangun untuk PT Wangsa Jatra Lestari (Tiga Serangkai Group) guna membantu Supervisor Procurement memilih supplier terbaik secara objektif menggunakan metode Analytic Hierarchy Process (AHP). Sistem berjalan pada lingkungan localhost (XAMPP/Laragon) dengan stack Laravel 11 sebagai aplikasi utama, Prisma ORM (Node.js sidecar) untuk akses MySQL 8.x, Blade + Tailwind CSS + Alpine.js untuk UI, serta Chart.js untuk visualisasi.

Sistem mencakup lima modul: Autentikasi multi-role, Data Master (Supplier, Produk, Kriteria, Subkriteria), Perhitungan AHP (pairwise comparison, normalisasi, konsistensi, ranking), Pengadaan (Purchase Order dengan rekap aktual otomatis), dan Laporan. Pendekatan pemilihan supplier bersifat hybrid: data aktual pengadaan (persentase kualitas, persentase keterlambatan) ditampilkan sebagai referensi bagi Supervisor saat melakukan pairwise comparison antar supplier, namun nilai pairwise tetap diinput manual berdasarkan judgement Supervisor.

## Glossary

- **SPK_System**: Sistem Pendukung Keputusan Pemilihan Supplier secara keseluruhan, terdiri dari aplikasi Laravel dan layanan Prisma sidecar.
- **Auth_Module**: Komponen autentikasi dan otorisasi berbasis session Laravel yang mengelola login, logout, dan middleware role.
- **Master_Module**: Komponen yang mengelola data master Supplier, Produk, Kriteria, dan Subkriteria.
- **AHP_Module**: Komponen yang menjalankan kalkulasi AHP termasuk pairwise comparison, normalisasi, lambda max, CI, CR, dan ranking.
- **PO_Module**: Komponen pengelolaan Purchase Order dan input data aktual penerimaan barang.
- **Report_Module**: Komponen pelaporan yang menyajikan laporan penilaian supplier, laporan pengadaan, dan profil supplier.
- **Prisma_Service**: Sidecar Node.js yang dipanggil oleh Laravel untuk operasi CRUD terhadap MySQL melalui Prisma ORM.
- **Pengadaan_Observer**: Laravel Eloquent Observer yang memanggil Prisma_Service ketika record pengadaan dibuat atau diperbarui untuk merekap statistik supplier.
- **Supervisor**: Pengguna dengan role Supervisor Procurement.
- **Sales**: Pengguna dengan role Sales Marketing.
- **Logistik**: Pengguna dengan role Staff Logistik.
- **Pairwise_Comparison**: Matriks perbandingan berpasangan menggunakan skala Saaty 1 sampai 9 dan resiprokalnya 1/2 sampai 1/9.
- **CR**: Consistency Ratio, hasil pembagian Consistency Index (CI) dengan Random Index (RI). Threshold konsisten adalah CR ≤ 0.1.
- **CI**: Consistency Index, dihitung dengan rumus (lambda_max - n) / (n - 1).
- **Lambda_Max**: Rata-rata nilai eigen lambda_i dari matriks pairwise yang ternormalisasi.
- **RI**: Random Index, nilai konstanta tabel Saaty untuk n = 1 sampai 10.
- **Bobot_Wi**: Bobot prioritas hasil rata-rata baris matriks ternormalisasi.
- **Nilai_Akhir**: Skor akhir supplier, dihitung sebagai sigma (Bobot Kriteria x Bobot Subkriteria x Bobot Supplier per Subkriteria).
- **Persen_Kualitas**: Produk diterima baik dibagi produk dibeli dikali 100 persen.
- **Hari_Keterlambatan**: Selisih hari antara Tanggal Kedatangan aktual dan Tanggal PO.
- **PO**: Purchase Order, dokumen pengadaan barang ke supplier.

## Requirements

### Requirement 1: Autentikasi dan Otorisasi Multi-Role

**User Story:** Sebagai pengguna sistem, saya ingin login menggunakan akun sesuai role saya, sehingga saya hanya dapat mengakses fitur yang sesuai dengan tanggung jawab saya.

#### Acceptance Criteria

1. WHEN pengguna mengirimkan kredensial valid melalui halaman login, THE Auth_Module SHALL membuat session Laravel dan mengarahkan pengguna ke dashboard sesuai role.
2. WHEN pengguna dengan role Supervisor berhasil login, THE Auth_Module SHALL mengarahkan pengguna ke dashboard Supervisor Procurement.
3. WHEN pengguna dengan role Sales berhasil login, THE Auth_Module SHALL mengarahkan pengguna ke dashboard Sales Marketing.
4. WHEN pengguna dengan role Logistik berhasil login, THE Auth_Module SHALL mengarahkan pengguna ke dashboard Staff Logistik.
5. IF kredensial yang dikirimkan tidak valid, THEN THE Auth_Module SHALL menampilkan pesan kesalahan dan tetap berada di halaman login.
6. THE Auth_Module SHALL menyimpan password menggunakan algoritma bcrypt sebelum data_akun dipersist ke database.
7. WHEN pengguna yang belum terautentikasi mengakses URL terproteksi, THE Auth_Module SHALL mengalihkan pengguna ke halaman login.
8. IF pengguna terautentikasi mengakses URL yang bukan untuk role-nya, THEN THE Auth_Module SHALL menolak akses dan mengembalikan respons HTTP 403.
9. WHEN pengguna menekan tombol logout, THE Auth_Module SHALL menghapus session Laravel dan mengalihkan pengguna ke halaman login.
10. THE Auth_Module SHALL menyertakan token CSRF pada setiap form yang melakukan modifikasi data.
11. THE Auth_Module SHALL tidak menyediakan halaman registrasi mandiri maupun mekanisme lupa password.

### Requirement 2: Manajemen Data Master Supplier dan Produk

**User Story:** Sebagai Supervisor, saya ingin mengelola data supplier dan produk, sehingga sistem memiliki referensi entitas yang dapat dinilai dan dipesan.

#### Acceptance Criteria

1. THE Master_Module SHALL menyediakan operasi Create, Read, Update, dan Delete untuk entitas data_supplier kepada Supervisor.
2. THE Master_Module SHALL menyediakan operasi Create, Read, Update, dan Delete untuk entitas data_produk kepada Supervisor.
3. WHEN Supervisor mengirimkan form pembuatan supplier dengan data valid, THE Master_Module SHALL memanggil Prisma_Service untuk memasukkan record ke tabel data_supplier dan menampilkan supplier tersebut di daftar.
4. WHEN Supervisor mengirimkan form pembuatan produk dengan data valid, THE Master_Module SHALL memanggil Prisma_Service untuk memasukkan record ke tabel data_produk dan menampilkan produk tersebut di daftar.
5. IF Supervisor mengirimkan form supplier atau produk dengan field wajib kosong, THEN THE Master_Module SHALL menolak penyimpanan dan menampilkan pesan validasi pada field terkait.
6. WHEN Supervisor menghapus supplier yang masih direferensikan oleh data_pengadaan atau penilaian_supplier, THE Master_Module SHALL menolak penghapusan dan menampilkan pesan bahwa supplier sedang digunakan.
7. WHERE pengguna memiliki role Sales atau Logistik, THE Master_Module SHALL hanya mengizinkan akses baca pada data supplier dan produk.

### Requirement 3: Manajemen Kriteria dan Subkriteria

**User Story:** Sebagai Supervisor, saya ingin mengelola kriteria dan subkriteria penilaian, sehingga proses AHP dapat dijalankan berdasarkan struktur hierarki yang benar.

#### Acceptance Criteria

1. THE Master_Module SHALL menginisialisasi data_kriteria dengan lima kriteria default: Cost, Quality, Delivery, Service, dan Repair Service.
2. THE Master_Module SHALL menyediakan operasi Create, Read, Update, dan Delete untuk data_kriteria kepada Supervisor.
3. THE Master_Module SHALL menyediakan operasi Create, Read, Update, dan Delete untuk data_subkriteria yang terhubung ke kriteria induknya.
4. WHEN Supervisor membuat subkriteria, THE Master_Module SHALL meminta referensi kriteria induk dan menyimpannya melalui Prisma_Service.
5. IF Supervisor menghapus kriteria yang masih memiliki subkriteria atau penilaian aktif, THEN THE Master_Module SHALL menolak penghapusan dan menampilkan pesan bahwa kriteria masih digunakan.
6. THE Master_Module SHALL menampilkan daftar subkriteria yang dikelompokkan berdasarkan kriteria induk.

### Requirement 4: Pairwise Comparison Kriteria dan Subkriteria

**User Story:** Sebagai Supervisor, saya ingin melakukan pairwise comparison antar kriteria dan antar subkriteria, sehingga sistem dapat menghitung bobot prioritas tiap level hierarki.

#### Acceptance Criteria

1. THE AHP_Module SHALL menampilkan halaman input pairwise comparison antar kriteria sebagai langkah pertama dari urutan Kriteria, Subkriteria, Supplier, Hasil.
2. THE AHP_Module SHALL menyediakan number selector dengan skala Saaty 1 sampai 9 dan resiprokal 1/2 sampai 1/9 untuk setiap pasangan kriteria.
3. WHEN Supervisor menyimpan pairwise comparison kriteria, THE AHP_Module SHALL memvalidasi bahwa seluruh pasangan unik telah diisi sebelum penyimpanan ke penilaian_kriteria.
4. THE AHP_Module SHALL menampilkan halaman input pairwise comparison antar subkriteria untuk setiap kriteria yang memiliki lebih dari satu subkriteria.
5. WHEN Supervisor menyimpan pairwise comparison subkriteria, THE AHP_Module SHALL menyimpan hasil ke penilaian_subkriteria melalui Prisma_Service.
6. THE AHP_Module SHALL menampilkan progress indicator yang menandai langkah aktif di antara Kriteria, Subkriteria, Supplier, dan Hasil.

### Requirement 5: Pairwise Comparison Supplier dengan Referensi Data Aktual

**User Story:** Sebagai Supervisor, saya ingin melihat data aktual supplier sambil melakukan pairwise comparison antar supplier per subkriteria, sehingga keputusan saya didukung oleh data nyata namun tetap mencerminkan judgement saya.

#### Acceptance Criteria

1. THE AHP_Module SHALL menampilkan halaman pairwise comparison antar supplier untuk setiap subkriteria yang aktif.
2. THE AHP_Module SHALL menampilkan rekap data aktual setiap supplier yang terdiri dari Total Persen Cacat, Total Persen Keterlambatan, dan Mean Hari Keterlambatan sebagai referensi pada halaman pairwise supplier.
3. THE AHP_Module SHALL menyediakan number selector dengan skala Saaty 1 sampai 9 dan resiprokal untuk setiap pasangan supplier.
4. WHEN Supervisor menyimpan pairwise comparison supplier, THE AHP_Module SHALL menyimpan nilai pairwise yang diinput manual ke penilaian_supplier melalui Prisma_Service.
5. THE AHP_Module SHALL menggunakan nilai pairwise yang diinput Supervisor sebagai sumber kebenaran untuk perhitungan bobot supplier, tanpa menggantinya dengan data aktual.

### Requirement 6: Kalkulasi AHP dan Pemeriksaan Konsistensi

**User Story:** Sebagai Supervisor, saya ingin sistem menghitung bobot, lambda max, CI, dan CR dari setiap matriks pairwise, sehingga saya dapat memastikan judgement saya konsisten sebelum melihat ranking.

#### Acceptance Criteria

1. WHEN matriks pairwise telah lengkap diisi, THE AHP_Module SHALL menormalisasi matriks dengan membagi setiap nilai dengan total kolomnya.
2. THE AHP_Module SHALL menghitung Bobot_Wi sebagai rata-rata baris dari matriks ternormalisasi.
3. THE AHP_Module SHALL menghitung Lambda_Max sebagai rata-rata nilai lambda_i dari matriks pairwise.
4. THE AHP_Module SHALL menghitung CI menggunakan rumus (Lambda_Max - n) dibagi (n - 1) dengan n adalah jumlah elemen yang dibandingkan.
5. THE AHP_Module SHALL menghitung CR menggunakan rumus CI dibagi RI dengan RI diambil dari tabel Saaty untuk n sama dengan 1 hingga 10.
6. WHEN CR yang dihitung kurang dari atau sama dengan 0.1, THE AHP_Module SHALL menandai matriks sebagai konsisten dan mengizinkan Supervisor melanjutkan ke langkah berikutnya.
7. IF CR yang dihitung lebih besar dari 0.1, THEN THE AHP_Module SHALL menampilkan peringatan inkonsistensi dan meminta Supervisor menginput ulang nilai pairwise sebelum melanjutkan.
8. WHEN seluruh kalkulasi selesai dijalankan untuk n kurang dari atau sama dengan 10 supplier, THE AHP_Module SHALL menyelesaikan kalkulasi dalam waktu kurang dari 2 detik.

### Requirement 7: Hasil AHP dan Ranking Supplier

**User Story:** Sebagai Supervisor, saya ingin melihat ranking supplier beserta nilai akhir dan visualisasi bobot, sehingga saya dapat menetapkan supplier terpilih.

#### Acceptance Criteria

1. THE AHP_Module SHALL menghitung Nilai_Akhir tiap supplier sebagai sigma dari (Bobot Kriteria dikali Bobot Subkriteria dikali Bobot Supplier per Subkriteria).
2. THE AHP_Module SHALL menyimpan Nilai_Akhir dan urutan ranking ke tabel data_hasil_ahp melalui Prisma_Service.
3. THE AHP_Module SHALL menampilkan tabel ranking supplier diurutkan dari Nilai_Akhir tertinggi ke terendah.
4. THE Report_Module SHALL menampilkan bar chart ranking supplier menggunakan Chart.js pada halaman hasil AHP.
5. THE Report_Module SHALL menampilkan donut chart bobot kriteria menggunakan Chart.js pada halaman hasil AHP.

### Requirement 8: Pengelolaan Purchase Order oleh Sales

**User Story:** Sebagai Sales Marketing, saya ingin menginput dan mengelola Purchase Order beserta dokumentasi foto, sehingga proses pengadaan tercatat lengkap.

#### Acceptance Criteria

1. THE PO_Module SHALL menyediakan operasi Create, Read, Update, dan Delete untuk data_pengadaan kepada Sales.
2. WHEN Sales membuat PO baru, THE PO_Module SHALL meminta referensi supplier, produk, jumlah dibeli, tanggal PO, dan dokumentasi foto.
3. WHEN Sales mengunggah foto laporan PO, THE PO_Module SHALL menyimpan file pada storage Laravel dan mereferensikan path-nya pada record data_pengadaan.
4. IF Sales mengirimkan PO tanpa supplier, produk, jumlah dibeli, atau tanggal PO, THEN THE PO_Module SHALL menolak penyimpanan dan menampilkan pesan validasi.
5. WHERE pengguna berperan sebagai Supervisor, THE PO_Module SHALL menyediakan akses baca terhadap seluruh PO.

### Requirement 9: Input Data Aktual oleh Logistik dan Rekap Otomatis

**User Story:** Sebagai Staff Logistik, saya ingin menginput data aktual penerimaan barang, sehingga sistem dapat menghitung persentase kualitas, hari keterlambatan, dan rekap supplier secara otomatis.

#### Acceptance Criteria

1. THE PO_Module SHALL menyediakan form input data aktual yang berisi tanggal kedatangan, jumlah diterima, dan jumlah cacat untuk setiap PO yang sudah ada kepada Logistik.
2. WHEN Logistik menyimpan data aktual sebuah PO, THE PO_Module SHALL menghitung Persen_Kualitas sebagai (jumlah diterima dikurangi jumlah cacat) dibagi jumlah dibeli dikali 100 persen.
3. WHEN Logistik menyimpan data aktual sebuah PO, THE PO_Module SHALL menghitung Hari_Keterlambatan sebagai selisih hari antara tanggal kedatangan dan tanggal PO.
4. IF jumlah diterima atau jumlah cacat yang diinput melebihi jumlah dibeli, THEN THE PO_Module SHALL menolak penyimpanan dan menampilkan pesan validasi.
5. WHEN record data_pengadaan dibuat atau diperbarui, THE Pengadaan_Observer SHALL memanggil Prisma_Service untuk memperbarui Mean Hari Keterlambatan, Total Persen Cacat, dan Total Persen Keterlambatan pada data_supplier terkait.

### Requirement 10: Pelaporan dan Profil Supplier

**User Story:** Sebagai pengguna, saya ingin melihat laporan penilaian supplier, laporan pengadaan, dan profil supplier, sehingga saya memiliki gambaran lengkap kinerja tiap supplier.

#### Acceptance Criteria

1. THE Report_Module SHALL menampilkan laporan penilaian supplier yang berisi ranking, Nilai_Akhir, dan bobot kriteria dari hasil AHP terkini.
2. THE Report_Module SHALL menampilkan laporan pengadaan yang berisi daftar PO beserta supplier, produk, jumlah, tanggal PO, tanggal kedatangan, Persen_Kualitas, dan Hari_Keterlambatan.
3. THE Report_Module SHALL menampilkan halaman profil supplier yang berisi data master supplier dan rekap Mean Hari Keterlambatan, Total Persen Cacat, serta Total Persen Keterlambatan.
4. WHERE pengguna berperan sebagai Supervisor, THE Report_Module SHALL menyediakan tombol untuk mengajukan laporan penilaian.
5. WHERE pengguna berperan sebagai Sales atau Logistik, THE Report_Module SHALL menyediakan akses baca terhadap laporan penilaian supplier, laporan pengadaan, dan profil supplier.

### Requirement 11: Persistensi Data via Prisma Sidecar

**User Story:** Sebagai pengembang sistem, saya ingin seluruh akses database dilakukan melalui Prisma sidecar, sehingga skema database konsisten dengan Prisma schema.

#### Acceptance Criteria

1. THE SPK_System SHALL mengakses MySQL 8.x hanya melalui Prisma_Service untuk operasi tulis pada tabel data_akun, data_kriteria, data_subkriteria, penilaian_kriteria, penilaian_subkriteria, data_supplier, data_produk, penilaian_supplier, data_hasil_ahp, dan data_pengadaan.
2. THE Prisma_Service SHALL mengekspos sepuluh tabel yang sama saat dibuka melalui Prisma Studio.
3. WHEN Laravel memanggil Prisma_Service untuk satu operasi query tunggal, THE Prisma_Service SHALL menyelesaikan operasi dalam waktu kurang dari 500 milidetik untuk dataset hingga 10 supplier dan 100 PO.
4. IF Prisma_Service tidak tersedia atau mengembalikan kesalahan, THEN THE SPK_System SHALL menampilkan pesan kesalahan kepada pengguna dan tidak melakukan perubahan parsial pada data.

### Requirement 12: Antarmuka Pengguna dan Responsivitas

**User Story:** Sebagai pengguna, saya ingin antarmuka yang konsisten dan dapat digunakan di perangkat mobile, sehingga saya dapat bekerja dari berbagai perangkat di lingkungan localhost kantor.

#### Acceptance Criteria

1. THE SPK_System SHALL menggunakan warna primer teal #009688 dan dark teal #00695C serta tipografi Inter atau Poppins pada seluruh halaman.
2. WHILE viewport memiliki lebar 1024 piksel atau lebih, THE SPK_System SHALL menampilkan sidebar tetap dengan lebar 280 piksel dan navbar sticky setinggi 80 piksel.
3. WHILE viewport memiliki lebar antara 375 piksel dan kurang dari 1024 piksel, THE SPK_System SHALL menampilkan sidebar dalam bentuk drawer yang dapat dibuka melalui tombol toggle.
4. THE AHP_Module SHALL menampilkan pairwise comparison sebagai card per pasangan dengan number selector berbentuk rounded teal, bukan tabel matriks penuh.
5. THE SPK_System SHALL berfungsi pada Chrome, Firefox, dan Edge versi rilis dua tahun terakhir.

### Requirement 13: Lingkup yang Dikecualikan

**User Story:** Sebagai pemangku kepentingan, saya ingin batas lingkup sistem ditetapkan secara eksplisit, sehingga tidak ada ekspektasi terhadap fitur di luar cakupan proyek.

#### Acceptance Criteria

1. THE SPK_System SHALL berjalan pada lingkungan localhost menggunakan XAMPP atau Laragon.
2. THE SPK_System SHALL menyediakan fitur autentikasi yang terbatas pada login dan logout, tanpa registrasi mandiri maupun pemulihan password.
3. THE SPK_System SHALL menyajikan laporan dalam format tampilan web tanpa fitur ekspor ke PDF maupun Excel.
4. THE SPK_System SHALL beroperasi tanpa integrasi API eksternal, notifikasi email, maupun notifikasi push.
5. THE SPK_System SHALL beroperasi tanpa modul analitik real-time dan tanpa modul audit log.
