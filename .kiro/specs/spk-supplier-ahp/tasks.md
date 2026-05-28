# Implementation Plan: SPK Supplier AHP

## Overview

Rencana implementasi ini menerjemahkan design.md menjadi serangkaian task code-generation yang inkremental untuk SPK Pemilihan Supplier PT Wangsa Jatra Lestari. Stack: Laravel 11 (UI, routing, business logic AHP) + Node.js sidecar (Express + Prisma + MySQL 8.x) yang berkomunikasi via HTTP loopback dengan shared secret. Setiap task membangun di atas task sebelumnya: pondasi proyek → schema Prisma → endpoint sidecar → repository Laravel → autentikasi → modul master → kalkulator AHP → UI pairwise → pengadaan dengan observer → laporan → seeder dan dokumentasi. Property-Based Tests (PHPUnit + `eris/eris`, Vitest + `fast-check` untuk sidecar) dijadwalkan sedekat mungkin dengan implementasi terkait agar regresi terdeteksi dini, dan setiap PBT secara eksplisit merujuk Property number serta requirement yang divalidasi.

## Tasks

- [ ] 1. Setup proyek dan infrastruktur dasar
  - [ ] 1.1 Inisialisasi Laravel 11 skeleton
    - Jalankan `composer create-project laravel/laravel .` di repo root, set timezone `Asia/Jakarta` dan locale `id` di `config/app.php`, install Guzzle (`composer require guzzlehttp/guzzle`).
    - Buat folder `app/Services/{Prisma,Ahp,Supplier}`, `app/Services/Prisma/Repositories`, `app/Observers`, dan struktur view per role di `resources/views/{auth,supervisor,sales,logistik,components/ui,components/layouts}` sesuai design.md.
    - _Requirements: 11.1, 13.1_

  - [ ] 1.2 Inisialisasi Node.js sidecar (Express + Prisma)
    - Buat folder `prisma-service/` dengan `package.json` (TypeScript, Express, `@prisma/client`, `prisma`, `dotenv`, `tsx`, Vitest), `tsconfig.json`, dan struktur `src/{server.ts,auth.ts,prismaClient.ts,routes/}` + `tests/`.
    - Tambahkan script `dev`, `start`, `test`, `prisma:studio`, `prisma:migrate` di `package.json`.
    - _Requirements: 11.1, 11.2_

  - [ ] 1.3 Konfigurasi `.env` Laravel dan sidecar
    - Tambahkan `PRISMA_BASE_URL=http://127.0.0.1:3001`, `PRISMA_SHARED_SECRET=<random32>` ke `.env` Laravel; buat `prisma-service/.env` dengan `DATABASE_URL="mysql://root:@127.0.0.1:3306/spk_supplier"`, `PRISMA_SHARED_SECRET=<sama>`, `PORT=3001`.
    - Daftarkan binding di `config/services.php` agar `PrismaClient` membaca variabel.
    - _Requirements: 11.1, 13.1_

  - [ ] 1.4 Definisi `prisma/schema.prisma` untuk 10 model
    - Tulis skema lengkap di `prisma-service/prisma/schema.prisma` untuk model: `Akun`, `Supplier`, `Produk`, `Kriteria`, `Subkriteria`, `PenilaianKriteria`, `PenilaianSubkriteria`, `PenilaianSupplier`, `HasilAhp`, `Pengadaan` dengan named relations (`PenilaianKriteriaA/B`, `psA/psB`, dst), enum `Role`, kolom rekap di `data_supplier`, dan `@@map` ke nama tabel snake_case sesuai design.md.
    - _Requirements: 11.1, 11.2_

  - [ ] 1.5 Jalankan `npx prisma migrate dev --name init`
    - Generate Prisma Client dan migration awal. Verifikasi 10 tabel terbentuk di MySQL `spk_supplier` dan dapat dibuka via `npx prisma studio`.
    - _Requirements: 11.1, 11.2_

  - [ ] 1.6 Konfigurasi Tailwind dengan token teal + Inter/Poppins
    - Install Tailwind via NPM, tulis `tailwind.config.js` dengan `colors.teal.{DEFAULT:#009688, dark:#00695C, light:#E0F2F1, 50, 500, 700}` dan `fontFamily.sans=['Inter','Poppins',...]`.
    - Tulis `resources/css/app.css` dengan directive `@tailwind base/components/utilities` + import Google Fonts Inter & Poppins.
    - _Requirements: 12.1_

  - [ ] 1.7 Layout Blade base (sidebar 280px + navbar 80px + drawer mobile)
    - Buat `resources/views/components/layouts/app.blade.php` (slot `header/sidebar/main`), `sidebar.blade.php` (lebar `280px` ≥1024px, drawer ≤1023px dengan Alpine `x-data="{open:false}"`), `navbar.blade.php` (sticky `h-[80px]`), `drawer.blade.php` (backdrop `bg-teal-dark/40`).
    - Pastikan breakpoint Tailwind `lg:` membedakan layout fixed vs drawer.
    - _Requirements: 12.2, 12.3_

- [ ] 2. Prisma sidecar (Express + Prisma Client)
  - [ ] 2.1 Bootstrap Express + middleware shared secret + endpoint `/healthz`
    - Tulis `src/server.ts` (bind `127.0.0.1:3001`), `src/auth.ts` middleware `requireSecret` yang membandingkan `X-Prisma-Secret` dengan `process.env.PRISMA_SHARED_SECRET`.
    - Endpoint `GET /healthz` mengembalikan `{ ok:true, prisma:'connected' }` setelah `prisma.$queryRaw\`SELECT 1\`` sukses.
    - _Requirements: 11.1, 11.4_

  - [ ] 2.2 PrismaClient singleton di sidecar
    - Tulis `src/prismaClient.ts` yang membuat instance `new PrismaClient()` tunggal dengan logging level `warn`/`error` dan graceful shutdown pada `SIGTERM`.
    - _Requirements: 11.1, 11.3_

  - [ ] 2.3 Endpoint `/akun`
    - Tulis `src/routes/akun.ts` dengan `GET /akun?username=` (lookup by unique), `POST /akun` (insert dengan `password_hash` apa adanya), `GET /akun/:id`. Daftarkan di `server.ts`.
    - _Requirements: 1.1, 1.6, 11.1_

  - [ ] 2.4 Endpoint `/suppliers` lengkap (CRUD + `with-rekap` + `rekap` PATCH)
    - Tulis `src/routes/supplier.ts` dengan `GET/POST/GET:id/PUT:id/DELETE:id`, `POST /suppliers/with-rekap` (body `{ids:number[]}`), `PATCH /suppliers/:id/rekap` (body `{mean_hari_keterlambatan,total_persen_cacat,total_persen_keterlambatan}`).
    - `DELETE /suppliers/:id` mengecek `pengadaan` dan `penilaian_supplier`; jika referenced, kembalikan 409 `{code:'REFERENCED', message}`.
    - _Requirements: 2.1, 2.6, 5.2, 9.5, 11.1_

  - [ ] 2.5 Endpoint `/produk`
    - Tulis `src/routes/produk.ts` (CRUD); `DELETE` mengecek referensi `pengadaan` dan kembalikan 409 jika dipakai.
    - _Requirements: 2.2, 2.6, 11.1_

  - [ ] 2.6 Endpoint `/kriteria`
    - Tulis `src/routes/kriteria.ts` (CRUD); `DELETE` mengecek referensi `subkriteria` dan `penilaian_kriteria`, kembalikan 409 jika ada.
    - _Requirements: 3.2, 3.5, 11.1_

  - [ ] 2.7 Endpoint `/subkriteria`
    - Tulis `src/routes/subkriteria.ts` (CRUD); `GET /subkriteria?kriteria_id=` mendukung filter; `DELETE` mengecek referensi `penilaian_subkriteria`/`penilaian_supplier`, kembalikan 409 jika ada.
    - _Requirements: 3.3, 3.4, 3.6, 11.1_

  - [ ] 2.8 Endpoint `/penilaian-kriteria` (`replace` atomik + GET)
    - Tulis `src/routes/penilaian-kriteria.ts` dengan `POST /penilaian-kriteria/replace` body `{kriteriaIds:number[], matrix:number[][]}` yang membungkus `prisma.$transaction([deleteMany, createMany])`. Implementasi `GET /penilaian-kriteria` yang merekonstruksi matriks dari record.
    - _Requirements: 4.3, 11.1, 11.4_

  - [ ] 2.9 Endpoint `/penilaian-subkriteria` (`replace` atomik + GET)
    - Tulis `src/routes/penilaian-subkriteria.ts` dengan `POST /penilaian-subkriteria/replace` body `{kriteriaId,subkriteriaIds,matrix}` di dalam `$transaction`; `GET /penilaian-subkriteria/:kriteriaId`.
    - _Requirements: 4.5, 11.1, 11.4_

  - [ ] 2.10 Endpoint `/penilaian-supplier` (`replace` atomik + GET)
    - Tulis `src/routes/penilaian-supplier.ts` dengan `POST /penilaian-supplier/replace` body `{subkriteriaId,supplierIds,matrix}` di dalam `$transaction`; `GET /penilaian-supplier/:subkriteriaId`.
    - _Requirements: 5.4, 11.1, 11.4_

  - [ ] 2.11 Endpoint `/hasil-ahp` (`replace` atomik + GET)
    - Tulis `src/routes/hasil-ahp.ts` dengan `POST /hasil-ahp/replace` body `{entries:[{supplier_id,nilai_akhir,ranking}]}` di dalam `$transaction`; `GET /hasil-ahp` mengembalikan list ranking ASC.
    - _Requirements: 7.2, 11.1, 11.4_

  - [ ] 2.12 Endpoint `/pengadaan` + `/pengadaan/by-supplier/:id?actualOnly=1`
    - Tulis `src/routes/pengadaan.ts` (CRUD), parameter `actualOnly=1` memfilter hanya record dengan `tanggal_kedatangan IS NOT NULL`. Hitung `persen_kualitas` & `hari_keterlambatan` server-side saat data aktual disimpan agar tetap konsisten antar pemanggil.
    - _Requirements: 8.1, 9.2, 9.3, 9.5, 11.1_

  - [ ]* 2.13 Vitest smoke tests untuk sidecar
    - Tulis `prisma-service/tests/healthz.test.ts`, `tests/supplier.test.ts`, `tests/penilaian-replace.test.ts` yang memverifikasi shared secret middleware, status 401 tanpa header, dan jalur happy-path setiap endpoint.
    - _Requirements: 11.1, 11.3_

- [ ] 3. Integrasi Laravel ↔ Prisma sidecar dan repositories
  - [ ] 3.1 `PrismaClient` (Guzzle) + `PrismaException` + `PrismaServiceProvider`
    - Tulis `app/Services/Prisma/PrismaClient.php` (method `call($method,$path,$body)` dengan timeout 5 detik, header `X-Prisma-Secret`), `PrismaException.php` (preserve HTTP status + message i18n), `app/Providers/PrismaServiceProvider.php` (bind singleton).
    - Daftarkan exception renderer di `bootstrap/app.php` yang menampilkan `errors/prisma-down.blade.php` saat `PrismaException` dengan kode 0/connection.
    - _Requirements: 11.1, 11.4_

  - [ ] 3.2 `AkunRepository`
    - Tulis `app/Services/Prisma/Repositories/AkunRepository.php` dengan method `findByUsername`, `findById`, `create($data)`.
    - _Requirements: 1.1, 1.6, 11.1_

  - [ ] 3.3 `SupplierRepository` (CRUD + `listWithRekap` + `updateRekap`)
    - Tulis repository dengan `all`, `find`, `create`, `update`, `delete`, `countBySupplier` (pengadaan), `listWithRekap(array $ids)`, `updateRekap(int $id, array $payload)`.
    - _Requirements: 2.1, 2.6, 5.2, 9.5, 11.1_

  - [ ] 3.4 `ProdukRepository`
    - CRUD + `countByProduk` untuk cek referensi sebelum delete.
    - _Requirements: 2.2, 2.6, 11.1_

  - [ ] 3.5 `KriteriaRepository`
    - CRUD + `countSubkriteria`, `countPenilaian`. Method `seedDefaults()` insert 5 default kriteria (`C/Cost`, `Q/Quality`, `D/Delivery`, `S/Service`, `R/Repair Service`) jika kosong.
    - _Requirements: 3.1, 3.2, 3.5, 11.1_

  - [ ] 3.6 `SubkriteriaRepository`
    - CRUD + `byKriteria(int $kriteriaId)`, `countPenilaian`.
    - _Requirements: 3.3, 3.4, 3.6, 11.1_

  - [ ] 3.7 `PenilaianKriteriaRepository`, `PenilaianSubkriteriaRepository`, `PenilaianSupplierRepository`
    - Tiga repository terpisah dengan `replace(array $payload)` (memanggil endpoint `/penilaian-*/replace`) dan `getMatrix(...)` yang merekonstruksi matriks numerik dari response sidecar.
    - _Requirements: 4.3, 4.5, 5.4, 11.1, 11.4_

  - [ ] 3.8 `HasilAhpRepository`
    - Method `replaceAll(array $entries)` dan `all()` (sorted by ranking).
    - _Requirements: 7.2, 11.1_

  - [ ] 3.9 `PengadaanRepository`
    - Method `all`, `find`, `create`, `update`, `delete`, `findActualBySupplier(int $supplierId)` (panggil `/pengadaan/by-supplier/:id?actualOnly=1`), `countBySupplier(int $supplierId)`, `countByProduk(int $produkId)`.
    - _Requirements: 8.1, 9.5, 11.1_

  - [ ]* 3.10 Property test pairwise persistence round-trip
    - **Property 7: Pairwise Persistence Round-Trip** — generate matriks valid `M (n×n)` (Saaty values mirrored, `M[i][i]=1`), simpan via `PenilaianKriteriaRepository::replace`/`PenilaianSubkriteriaRepository::replace`/`PenilaianSupplierRepository::replace`, baca kembali via `getMatrix`, assert max-error per cell ≤ 1e-9 untuk minimal 100 iterasi.
    - **Validates: Requirements 4.3, 4.4, 4.5, 5.4**

  - [ ]* 3.11 Property test atomicity Prisma failure
    - **Property 18: Atomicity Prisma Failure** — gunakan Guzzle `MockHandler` untuk mensimulasikan kegagalan di tengah `replace`, assert state repository tidak berubah parsial dan `PrismaException` dilempar; verifikasi controller tidak melakukan update domain lain saat terjadi kegagalan.
    - **Validates: Requirements 11.4**

- [ ] 4. Autentikasi dan otorisasi multi-role
  - [ ] 4.1 `Akun` POPO Authenticatable + `PrismaUserProvider`
    - Tulis `app/Auth/Akun.php` (final class implements `Illuminate\Contracts\Auth\Authenticatable`), `app/Auth/PrismaUserProvider.php` (`retrieveById`, `retrieveByCredentials`, `validateCredentials` via `Hash::check`).
    - Daftarkan provider di `config/auth.php` (`providers.users.driver=prisma`) dan `Auth::provider('prisma', fn() => new PrismaUserProvider(...))` di `AuthServiceProvider`.
    - _Requirements: 1.1, 1.6, 1.11_

  - [ ] 4.2 `LoginRequest` + `LoginController` (showForm, login, logout)
    - Tulis `app/Http/Requests/LoginRequest.php` (rule `username:required|string`, `password:required|string`).
    - Tulis `app/Http/Controllers/Auth/LoginController.php` dengan `showForm`, `login` (panggil `Auth::attempt`, regenerate session, redirect `/<role>/dashboard`), `logout` (invalidate + redirect `/login`). Pesan kesalahan saat kredensial invalid.
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.9, 1.10, 1.11_

  - [ ] 4.3 Middleware `EnsureRole` + route groups (`/supervisor`, `/sales`, `/logistik`)
    - Tulis `app/Http/Middleware/EnsureRole.php` (alias `role`) yang memvalidasi `auth()->user()->role === $role`, abort 403 jika berbeda, redirect `/login` jika unauthenticated.
    - Daftarkan alias di `bootstrap/app.php`. Tulis grouping di `routes/web.php` untuk `guest`, `auth`, dan tiga prefix role.
    - _Requirements: 1.7, 1.8, 2.7, 8.5, 10.5_

  - [ ] 4.4 View login (Shadcn UI port)
    - Tulis `resources/views/auth/login.blade.php` dengan komponen `<x-ui.input>`, `<x-ui.button>`, `<x-ui.alert>` dan token CSRF di form.
    - _Requirements: 1.1, 1.5, 1.10, 12.1_

  - [ ]* 4.5 Property test login redirect sesuai role
    - **Property 1: Login Redirect Sesuai Role** — generator `roleGen() ∈ {supervisor,sales,logistik}` dan akun valid, assert response 302 ke `/<role>/dashboard` dan session aktif untuk minimal 100 iterasi.
    - **Validates: Requirements 1.1, 1.2, 1.3, 1.4**

  - [ ]* 4.6 Property test password bcrypt round-trip
    - **Property 2: Password Hash Round-Trip** — generator string panjang 8..128, buat akun via repository, assert `password_hash` adalah hash bcrypt yang `Hash::check(plain, hash)===true`, dan plaintext tidak tersimpan di database.
    - **Validates: Requirements 1.6**

  - [ ]* 4.7 Property test authorization middleware
    - **Property 3: Authorization Middleware** — generator pasangan `(role, urlRequiredRole)`, assert 200/302 jika cocok, 403 jika tidak cocok, 302 ke `/login` jika tanpa session.
    - **Validates: Requirements 1.7, 1.8, 2.7, 8.5, 10.5**

- [ ] 5. Checkpoint - pondasi, sidecar, dan auth
  - Pastikan semua tests pass; jalankan `php artisan test` dan `npm --prefix prisma-service test`. Tanyakan ke user jika ada blocker pada konfigurasi MySQL/Laragon.

- [ ] 6. Modul Master (Supplier, Produk, Kriteria, Subkriteria)
  - [ ] 6.1 `SupplierController` + `SupplierStoreRequest` + Blade index/create/edit
    - Resource controller (route group supervisor) dengan CRUD memanggil `SupplierRepository`. Form Request validasi `kode:required|unique-via-prisma`, `nama:required|max:120`, `email:nullable|email`. View pakai komponen `<x-ui.table>`, `<x-ui.input>`, `<x-ui.modal>` untuk konfirmasi delete.
    - _Requirements: 2.1, 2.3, 2.5, 2.7_

  - [ ] 6.2 `ProdukController` + `ProdukStoreRequest` + Blade index/create/edit
    - Resource controller dengan CRUD via `ProdukRepository`. Form Request: `kode/nama/satuan:required`, `harga:required|numeric|min:0`.
    - _Requirements: 2.2, 2.4, 2.5, 2.7_

  - [ ] 6.3 `KriteriaController` + `KriteriaStoreRequest` + Blade + seed 5 default
    - Resource controller untuk CRUD; saat boot `PrismaServiceProvider` panggil `KriteriaRepository::seedDefaults()` jika tabel kosong (idempotent). View list kriteria + form create/edit.
    - _Requirements: 3.1, 3.2_

  - [ ] 6.4 `SubkriteriaController` + `SubkriteriaStoreRequest` + Blade grouped by kriteria
    - Resource controller; halaman index menampilkan subkriteria dikelompokkan per kriteria menggunakan accordion `<x-ui.card>`.
    - _Requirements: 3.3, 3.4, 3.6_

  - [ ] 6.5 Cascade-deletion guard (handle 409 dari sidecar)
    - Tulis `app/Exceptions/BusinessRuleException.php`. Modifikasi controller delete supplier/produk/kriteria/subkriteria: tangkap `PrismaException` dengan HTTP 409, flash error i18n ("Supplier sedang digunakan oleh N pengadaan"), redirect kembali ke index.
    - _Requirements: 2.6, 3.5_

  - [ ]* 6.6 Property test validator field wajib
    - **Property 4: Validator Field Wajib** — generator payload yang men-set salah satu field wajib menjadi `null`/string kosong untuk endpoint create supplier/produk/kriteria/subkriteria/PO, assert response 422 dan tidak ada record baru di sidecar (mock counter).
    - **Validates: Requirements 2.5, 8.4**

  - [ ]* 6.7 Property test cascade deletion guard
    - **Property 5: Cascade Deletion Guard** — generator state dengan supplier yang punya ≥1 pengadaan/penilaian_supplier (dan kriteria dengan ≥1 subkriteria/penilaian_kriteria), assert `DELETE` mengembalikan 409 dan record tetap ada.
    - **Validates: Requirements 2.6, 3.5**

- [ ] 7. AHP Calculation Services (pure PHP)
  - [ ] 7.1 `AhpCalculatorService` (normalisasi, weights, λmax, CI, CR, RI table)
    - Tulis `app/Services/Ahp/AhpCalculatorService.php` dengan konstanta `RI = [0.00,0.00,0.58,0.90,1.12,1.24,1.32,1.41,1.45,1.49]` dan method `calculate(array $A): AhpResult` mengembalikan `normalized`, `weights`, `lambdaMax`, `CI`, `CR`, `consistent` mengikuti pseudocode design.md.
    - Tulis DTO `app/Services/Ahp/AhpResult.php`.
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.8_

  - [ ] 7.2 `ConsistencyChecker`
    - Tulis `app/Services/Ahp/ConsistencyChecker.php` dengan method `isConsistent(AhpResult $result): bool` (`CR <= 0.10`) dan `lookupRi(int $n): float`.
    - _Requirements: 6.5, 6.6_

  - [ ]* 7.3 Property test AHP normalisasi & bobot
    - **Property 9: AHP Normalisasi dan Bobot** — generator `pairwiseMatrixGen(n)` (1..10), assert untuk hasil `calculate(A)`: setiap kolom `N` berjumlah `1.0` (tol 1e-9), `Σ W = 1.0` (tol 1e-9), `0 ≤ W[i] ≤ 1`. Minimal 100 iterasi.
    - **Validates: Requirements 6.1, 6.2**

  - [ ]* 7.4 Property test konsistensi sempurna
    - **Property 10: AHP Konsistensi Sempurna** — generator `consistentMatrixGen(n)` (vektor bobot positif → `A[i][j]=w_i/w_j`), assert `λ_max ≈ n`, `CI ≈ 0`, `CR ≈ 0` (tol 1e-6), `consistent==true`.
    - **Validates: Requirements 6.3, 6.4, 6.5**

  - [ ]* 7.5 Property test CR threshold 0.10
    - **Property 11: CR Threshold 0.10** — generator matriks valid `n ≥ 3`, assert `consistent === (CR ≤ 0.10)` dan controller AHP men-flash warning untuk `consistent==false`.
    - **Validates: Requirements 6.6, 6.7**

  - [ ] 7.6 `RankingService`
    - Tulis `app/Services/Ahp/RankingService.php` dengan method `computeRanking(array $supplierIds, array $kriteriaIds): array` yang mengambil pairwise dari repositories, memanggil `AhpCalculatorService` di tiga level, menghitung `nilai_akhir = Σ(Wk * Wsk * Ws)`, sort descending, dan persist via `HasilAhpRepository::replaceAll`.
    - _Requirements: 7.1, 7.2, 7.3, 6.8_

  - [ ]* 7.7 Property test hybrid invariant — bobot supplier independen rekap aktual
    - **Property 8: Hybrid Invariant** — generator dua state `S1`/`S2` identik di seluruh tabel `penilaian_*` namun berbeda kolom rekap aktual di `data_supplier`, assert `RankingService::computeRanking` menghasilkan ranking & nilai_akhir identik.
    - **Validates: Requirements 5.5**

  - [ ]* 7.8 Property test nilai akhir invariant
    - **Property 12: Nilai Akhir Invariant** — generator `Wk`, `Wsk_per_k`, `Ws_per_sub` (Σ=1 tiap level), assert hasil `RankingService` memenuhi `0 ≤ nilai_akhir[s] ≤ 1` dan `Σ_s nilai_akhir[s] = 1` (tol 1e-9).
    - **Validates: Requirements 7.1**

  - [ ]* 7.9 Property test ranking monotonic descending
    - **Property 13: Ranking Monotonic Descending** — generator pairwise valid, assert `ranking[i].nilai_akhir ≥ ranking[i+1].nilai_akhir` untuk semua `i` dan `ranking[0]` berisi nilai_akhir maksimum.
    - **Validates: Requirements 7.3**

- [ ] 8. AHP UI Layer
  - [ ] 8.1 Stepper Blade component (Kriteria → Subkriteria → Supplier → Hasil)
    - Tulis `resources/views/components/ui/stepper.blade.php` dengan prop `:active="'kriteria'|'subkriteria'|'supplier'|'hasil'"`, `aria-current="step"`, styling teal-dark untuk langkah aktif dan teal-light untuk langkah mendatang.
    - _Requirements: 4.6, 12.1_

  - [ ] 8.2 `PairwiseKriteriaController` + `PairwiseKriteriaRequest` + view
    - Controller GET (load matriks dari `PenilaianKriteriaRepository::getMatrix` + AhpCalculator preview), POST (validate + replace + redirect ke subkriteria jika konsisten). View merender card per pasangan dengan number selector skala Saaty (rounded teal) sesuai design.
    - _Requirements: 4.1, 4.2, 4.3, 6.1, 6.2, 12.4_

  - [ ] 8.3 `PairwiseSubkriteriaController` + `PairwiseSubkriteriaRequest` + view
    - Controller iterate per kriteria yang punya >1 subkriteria; tampilkan stepper langkah 2. Save via `PenilaianSubkriteriaRepository::replace`.
    - _Requirements: 4.4, 4.5, 4.6, 6.1, 6.2_

  - [ ] 8.4 `PairwiseSupplierController` + `PairwiseSupplierRequest` + view + Reference Panel
    - Controller iterate per subkriteria. View memuat Reference Panel read-only (`Total Persen Cacat`, `Total Persen Keterlambatan`, `Mean Hari Keterlambatan`) dari `SupplierRepository::listWithRekap`. Tidak ada handler JS yang menulis ke `<input>` pairwise.
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 12.4_

  - [ ]* 8.5 Property test Saaty scale validator
    - **Property 6: Saaty Scale Validator** — generator nilai numerik (mix valid Saaty ∪ noise), assert `PairwiseKriteriaRequest`/`PairwiseSubkriteriaRequest`/`PairwiseSupplierRequest` menerima jika dan hanya jika `v ∈ {1/9..1/2,1,2..9}` (tol 1e-6).
    - **Validates: Requirements 4.2, 5.3**

  - [ ] 8.6 `HasilAhpController` + Chart.js bar ranking + donut bobot kriteria
    - Controller membaca `HasilAhpRepository::all()` + bobot kriteria. View merender `<canvas id="chartRanking">` (bar) dan `<canvas id="chartBobotKriteria">` (donut) dengan dataset di `data-*` attribute, di-pickup oleh Alpine `x-data="ahpCharts(...)"` di `resources/js/app.js`.
    - _Requirements: 7.3, 7.4, 7.5_

- [ ] 9. Pengadaan / Evaluasi Module
  - [ ] 9.1 `PhotoUploadService`
    - Tulis `app/Services/Pengadaan/PhotoUploadService.php` yang menyimpan file ke `storage/app/public/pengadaan/{id}/{uuid}.{ext}` dengan validasi mime (`jpg|png|webp`) max 4 MB; jalankan `php artisan storage:link`.
    - _Requirements: 8.3_

  - [ ] 9.2 `PengadaanController` (Sales) + `PengadaanStoreRequest` + Blade index/create/edit
    - Resource controller di group `role:sales`. Form Request: `supplier_id/produk_id:required|exists`, `jumlah_dibeli:required|integer|min:1`, `tanggal_po:required|date`, `foto:nullable|image|max:4096`. Save via `PengadaanRepository::create` dan `PhotoUploadService`.
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

  - [ ] 9.3 `AktualPengadaanController` (Logistik) + `AktualPengadaanRequest` + Blade
    - Form Request: `tanggal_kedatangan:required|date`, `jumlah_diterima:required|integer|min:0|lte:jumlah_dibeli`, `jumlah_cacat:required|integer|min:0|lte:jumlah_diterima`. Halaman list PO yang belum punya tanggal_kedatangan + form input aktual.
    - _Requirements: 9.1, 9.4_

  - [ ] 9.4 Auto-calculate `persen_kualitas` + `hari_keterlambatan` saat save aktual
    - Modifikasi `AktualPengadaanController::update` untuk menghitung `persen_kualitas = (jumlah_diterima - jumlah_cacat) / jumlah_dibeli * 100` dan `hari_keterlambatan = floor((tanggal_kedatangan - tanggal_po) / 1 day)` (signed) sebelum memanggil `PengadaanRepository::update`.
    - _Requirements: 9.2, 9.3_

  - [ ] 9.5 `PengadaanSaved`/`PengadaanDeleted` Events + Listener (PengadaanObserver pattern)
    - Tulis `app/Events/PengadaanSaved.php`, `PengadaanDeleted.php`, `app/Listeners/PengadaanObserver.php` yang menerima event dan memanggil `RekapSupplierService::recalculateForSupplier($supplierId)`. Daftarkan di `app/Providers/EventServiceProvider.php`. Trigger event eksplisit dari `PengadaanController::store/update/destroy` dan `AktualPengadaanController::update` setelah Prisma commit sukses.
    - _Requirements: 9.5_

  - [ ] 9.6 `RekapSupplierService::recalculateForSupplier`
    - Tulis `app/Services/Supplier/RekapSupplierService.php` dengan method `recalculateForSupplier(int $supplierId)`: ambil rows aktual via `PengadaanRepository::findActualBySupplier`, hitung `mean_hari_keterlambatan = sumHari/count`, `total_persen_cacat = totalCacat/totalDibeli*100`, `total_persen_keterlambatan = countTerlambat/count*100`, jika kosong → semua 0; persist via `SupplierRepository::updateRekap`.
    - _Requirements: 9.5_

  - [ ] 9.7 Halaman Evaluasi Aktual + filter periode
    - Tulis view Logistik `resources/views/logistik/aktual/index.blade.php` dengan filter range tanggal (`tanggal_po >= from`, `<= to`) dan tampilan persen_kualitas/hari_keterlambatan per PO.
    - _Requirements: 9.1, 9.2, 9.3, 10.2_

  - [ ]* 9.8 Property test persen kualitas formula & range
    - **Property 14: Persen Kualitas Formula dan Range** — generator `validQuantityGen()` triplet `(dibeli, diterima, cacat)` dengan `0 ≤ cacat ≤ diterima ≤ dibeli` dan `dibeli > 0`, assert `persen_kualitas == (diterima-cacat)/dibeli*100` dan `0 ≤ persen_kualitas ≤ 100`.
    - **Validates: Requirements 9.2**

  - [ ]* 9.9 Property test hari keterlambatan formula
    - **Property 15: Hari Keterlambatan Formula** — generator `dateRangeGen()` pasangan tanggal ±2 tahun, assert `hari_keterlambatan == floor((tanggal_kedatangan - tanggal_po) / 1 day)` dalam hari kalender (signed).
    - **Validates: Requirements 9.3**

  - [ ]* 9.10 Property test validator aktual quantity
    - **Property 16: Validator Aktual Quantity** — generator triplet termasuk pelanggaran `diterima > dibeli` atau `cacat > diterima`, assert `AktualPengadaanRequest` menerima ⇔ `0 ≤ diterima ≤ dibeli` ∧ `0 ≤ cacat ≤ diterima`. Jika ditolak, kolom turunan tidak diperbarui.
    - **Validates: Requirements 9.4**

  - [ ]* 9.11 Property test observer rekap konsisten dengan agregasi PO
    - **Property 17: Observer Rekap** — generator urutan operasi (insert/update/delete) PO untuk supplier `s`, assert setelah handler selesai, kolom rekap di `data_supplier[s]` sama dengan recompute langsung dari list PO aktual: `mean_hari_keterlambatan`, `total_persen_cacat`, `total_persen_keterlambatan` (atau 0 jika kosong).
    - **Validates: Requirements 9.5**

- [ ] 10. Modul Laporan
  - [ ] 10.1 `LaporanPenilaianController` + view
    - Membaca `HasilAhpRepository::all()` + bobot kriteria; view menampilkan tabel ranking + Nilai_Akhir + chart bobot kriteria (reuse komponen Chart.js dari hasil AHP).
    - _Requirements: 10.1, 7.4, 7.5_

  - [ ] 10.2 `LaporanPengadaanController` + view
    - Membaca daftar PO dengan join supplier+produk; view menampilkan kolom supplier, produk, jumlah_dibeli, tanggal_po, tanggal_kedatangan, persen_kualitas, hari_keterlambatan dengan filter periode.
    - _Requirements: 10.2_

  - [ ] 10.3 `ProfilSupplierController` + view
    - Membaca data master supplier + rekap (`mean_hari_keterlambatan`, `total_persen_cacat`, `total_persen_keterlambatan`) dan list PO terkait; tampilkan halaman profil per supplier.
    - _Requirements: 10.3_

  - [ ] 10.4 Tombol "Ajukan laporan" Supervisor only
    - Modifikasi view `laporan/penilaian.blade.php` agar tombol hanya muncul `@if(auth()->user()->role === 'supervisor')`. Untuk Sales/Logistik, hanya akses baca.
    - _Requirements: 10.4, 10.5_

- [ ] 11. Final setup, seeder, dokumentasi
  - [ ] 11.1 `AkunSeeder` (3 default akun) via `PrismaClient`
    - Tulis `database/seeders/AkunSeeder.php` yang men-hash password dengan `Hash::make` dan memanggil `AkunRepository::create` untuk akun default `supervisor` (`spv1`), `sales` (`sales1`), `logistik` (`log1`). Daftarkan di `DatabaseSeeder`. Dokumentasikan kredensial default di README.
    - _Requirements: 1.1, 1.6_

  - [ ] 11.2 Smoke test integrasi
    - Tulis `tests/Feature/SmokeTest.php` yang memverifikasi: 5 kriteria default ter-seed (Cost/Quality/Delivery/Service/Repair Service), `GET /healthz` sidecar OK, `GET /login` render halaman, `GET /supervisor/dashboard` tanpa session redirect ke `/login`. Verifikasi manual `npx prisma studio` menampilkan 10 tabel.
    - _Requirements: 3.1, 11.2, 11.4, 13.1_

  - [ ] 11.3 README + skrip `composer run dev`
    - Tulis `README.md` (instalasi: clone, `composer install`, `npm install`, `npm --prefix prisma-service install`, set `.env`, `npx prisma migrate dev`, `php artisan migrate --seed`, `php artisan storage:link`). Tambahkan `composer.json` script `dev` yang memakai `concurrently` (atau `npx concurrently`) untuk menjalankan `php artisan serve` + `npm --prefix prisma-service run dev`.
    - _Requirements: 11.1, 13.1_

  - [ ] 11.4 Panduan per role di README
    - Tambahkan section README dengan flow Supervisor (master → pairwise kriteria → subkriteria → supplier → hasil → laporan), Sales (input PO), Logistik (input data aktual + halaman evaluasi). Sertakan screenshot path dan contoh skala Saaty.
    - _Requirements: 1.2, 1.3, 1.4, 4.6, 8.1, 9.1, 10.1, 10.2, 10.3_

- [ ] 12. Final checkpoint - Pastikan semua tests pass
  - Jalankan `php artisan test`, `npm --prefix prisma-service test`, dan smoke test manual end-to-end (login 3 role, buat supplier, buat PO, input aktual, jalankan AHP, lihat ranking dan laporan). Tanyakan ke user jika ada blocker.

## Notes

- Task ber-postfix `*` adalah opsional (PBT/test) dan dapat dilewati untuk MVP cepat tanpa menghilangkan fitur inti.
- Setiap task PBT secara eksplisit mereferensikan **Property N** dari design.md dan **Requirements** yang divalidasi sehingga ketertelusuran (traceability) terjaga.
- Property tests (`@group property`) menggunakan PHPUnit + `eris/eris` untuk Laravel dan Vitest + `fast-check` untuk sidecar dengan minimal 100 iterasi per property.
- Generator yang dapat dipakai ulang: `pairwiseMatrixGen(n)`, `consistentMatrixGen(n)`, `validQuantityGen()`, `dateRangeGen()`, `roleGen()` ditempatkan di `tests/Support/Generators.php`.
- Cascade-deletion guard dijalankan di sidecar (returns 409) dan ditangkap controller Laravel untuk menampilkan pesan i18n.
- Atomicity multi-step (`/penilaian-*/replace`, `/hasil-ahp/replace`, `DELETE /suppliers/:id`) dijamin oleh `prisma.$transaction` di sidecar.
- Reference Panel pada pairwise supplier adalah read-only — tidak ada handler JS yang menulis ke input pairwise (memenuhi Property 8 / Req 5.5).
- Stack: Laravel 11 + PHP 8.2+, Node.js ≥ 18, MySQL 8.x, Tailwind 3.x, Alpine.js 3.x, Chart.js 4.x.

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2"] },
    { "id": 1, "tasks": ["1.3", "1.4", "1.6"] },
    { "id": 2, "tasks": ["1.5", "1.7", "2.1"] },
    { "id": 3, "tasks": ["2.2"] },
    { "id": 4, "tasks": ["2.3", "2.4", "2.5", "2.6", "2.7", "2.8", "2.9", "2.10", "2.11", "2.12"] },
    { "id": 5, "tasks": ["2.13", "3.1"] },
    { "id": 6, "tasks": ["3.2", "3.3", "3.4", "3.5", "3.6", "3.7", "3.8", "3.9", "7.1", "7.2"] },
    { "id": 7, "tasks": ["3.10", "3.11", "4.1", "7.3", "7.4", "7.5", "7.6"] },
    { "id": 8, "tasks": ["4.2", "4.3", "4.4", "7.7", "7.8", "7.9", "8.1"] },
    { "id": 9, "tasks": ["4.5", "4.6", "4.7", "6.1", "6.2", "6.3", "6.4", "8.2", "8.3", "8.4", "8.6", "9.1"] },
    { "id": 10, "tasks": ["6.5", "6.6", "6.7", "8.5", "9.2", "9.3", "9.4", "9.6"] },
    { "id": 11, "tasks": ["9.5", "9.7", "9.8", "9.9", "9.10", "10.1", "10.2", "10.3"] },
    { "id": 12, "tasks": ["9.11", "10.4", "11.1"] },
    { "id": 13, "tasks": ["11.2", "11.3", "11.4"] }
  ]
}
```

## Workflow Selesai

Workflow `fast-task` untuk spec **spk-supplier-ahp** sudah lengkap. Tiga artefak telah tersedia:

- `requirements.md` (13 requirements)
- `design.md` (arsitektur Laravel + Prisma sidecar, 10 Prisma model, 18 correctness properties, testing strategy)
- `tasks.md` (rencana implementasi dengan DAG 14 wave)

Untuk mulai eksekusi, buka `tasks.md` dan klik **Start task** di samping task yang ingin dijalankan. Direkomendasikan mengikuti urutan wave 0 → 13 untuk memaksimalkan paralelisme dan menjaga dependency graph.
