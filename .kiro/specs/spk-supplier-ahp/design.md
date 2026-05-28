# Design Document

## Overview

Sistem SPK Pemilihan Supplier PT Wangsa Jatra Lestari diimplementasikan sebagai aplikasi monolitik Laravel 11 yang berjalan di lingkungan localhost (XAMPP/Laragon), dengan satu sidecar Node.js (≥ v18) yang menyediakan akses MySQL 8.x melalui Prisma ORM. Laravel bertindak sebagai aplikasi utama (UI, routing, autentikasi, otorisasi, validasi, business logic AHP), sementara sidecar Prisma_Service bertanggung jawab atas seluruh operasi persistensi domain ke MySQL.

Pendekatan dua-proses ini dipilih untuk memenuhi kebutuhan akademis (skema database konsisten dengan Prisma schema, dapat dibuka dengan Prisma Studio) tanpa harus memporting Laravel ke ekosistem Node. Komunikasi antar dua proses dilakukan melalui HTTP (loopback `http://127.0.0.1:3001`) menggunakan format JSON, dilindungi oleh shared secret yang dimuat dari `.env`.

UI dibangun dengan Blade + Tailwind CSS, komponen Shadcn UI yang di-port menjadi Blade components, Alpine.js untuk interaksi ringan (toggle drawer, stepper state), Chart.js untuk visualisasi ranking dan bobot kriteria, serta Lucide Icons.

Lima modul fungsional diturunkan langsung dari requirements:

| Modul | Tanggung jawab utama |
|---|---|
| Auth_Module | Login, logout, session, middleware role |
| Master_Module | CRUD supplier, produk, kriteria, subkriteria |
| AHP_Module | Pairwise comparison, normalisasi, CI/CR, ranking |
| PO_Module | Purchase Order + input data aktual + observer rekap |
| Report_Module | Laporan penilaian, laporan pengadaan, profil supplier |

## Architecture

### High-Level Architecture

```
+--------------------+        HTTP/JSON          +------------------------+
|   Browser          | <----------------------> |   Laravel 11           |
|   (Blade + Alpine  |     port 8000 (artisan)  |   - Auth (session)     |
|    + Tailwind +    |                          |   - Routing            |
|    Chart.js)       |                          |   - Form Request       |
|                    |                          |   - Service classes    |
+--------------------+                          |   - PrismaService      |
                                                |   - PengadaanObserver  |
                                                +-----------+------------+
                                                            |
                                                            | HTTP/JSON
                                                            | http://127.0.0.1:3001
                                                            | shared secret
                                                            v
                                                +------------------------+
                                                |  Node.js Sidecar       |
                                                |  (Express + Prisma     |
                                                |   Client)              |
                                                +-----------+------------+
                                                            |
                                                            | Prisma Engine
                                                            v
                                                +------------------------+
                                                |  MySQL 8.x             |
                                                |  (10 tabel domain)     |
                                                +------------------------+
```

### Project Structure

```
pt-wangsa-ahp/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/LoginController.php
│   │   │   ├── Supervisor/{Dashboard, Supplier, Produk, Kriteria, Subkriteria, AhpKriteria, AhpSubkriteria, AhpSupplier, AhpHasil, Laporan}Controller.php
│   │   │   ├── Sales/{Dashboard, Pengadaan}Controller.php
│   │   │   └── Logistik/{Dashboard, AktualPengadaan}Controller.php
│   │   ├── Middleware/
│   │   │   └── EnsureRole.php           # cek $request->user()->role
│   │   └── Requests/
│   │       ├── LoginRequest.php
│   │       ├── SupplierStoreRequest.php
│   │       ├── ProdukStoreRequest.php
│   │       ├── PairwiseKriteriaRequest.php
│   │       ├── PairwiseSubkriteriaRequest.php
│   │       ├── PairwiseSupplierRequest.php
│   │       ├── PengadaanStoreRequest.php
│   │       └── AktualPengadaanRequest.php
│   ├── Services/
│   │   ├── Prisma/
│   │   │   ├── PrismaClient.php          # HTTP client (Guzzle)
│   │   │   ├── PrismaException.php
│   │   │   └── Repositories/
│   │   │       ├── AkunRepository.php
│   │   │       ├── SupplierRepository.php
│   │   │       ├── ProdukRepository.php
│   │   │       ├── KriteriaRepository.php
│   │   │       ├── SubkriteriaRepository.php
│   │   │       ├── PenilaianKriteriaRepository.php
│   │   │       ├── PenilaianSubkriteriaRepository.php
│   │   │       ├── PenilaianSupplierRepository.php
│   │   │       ├── HasilAhpRepository.php
│   │   │       └── PengadaanRepository.php
│   │   ├── Ahp/
│   │   │   ├── AhpCalculatorService.php  # normalisasi, λmax, CI, CR
│   │   │   ├── ConsistencyChecker.php
│   │   │   └── RankingService.php
│   │   └── Supplier/
│   │       └── RekapSupplierService.php  # dipanggil observer
│   ├── Observers/
│   │   └── PengadaanObserver.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── PrismaServiceProvider.php     # bind PrismaClient + observer
├── resources/
│   ├── views/
│   │   ├── components/                    # Shadcn-port Blade components
│   │   │   ├── ui/{button, card, input, table, badge, modal, dropdown, alert, toast, stepper}.blade.php
│   │   │   └── layouts/{app, sidebar, navbar, drawer}.blade.php
│   │   ├── auth/login.blade.php
│   │   ├── supervisor/{dashboard, supplier, produk, kriteria, subkriteria, ahp/{kriteria, subkriteria, supplier, hasil}, laporan/{penilaian, profil}}.blade.php
│   │   ├── sales/{dashboard, pengadaan/{index, create, edit}}.blade.php
│   │   └── logistik/{dashboard, aktual/{index, edit}}.blade.php
│   ├── css/app.css                        # Tailwind directives + tokens teal
│   └── js/app.js                          # Alpine + Chart.js bootstrap
├── routes/
│   └── web.php                            # 4 group: /, /supervisor, /sales, /logistik
├── database/
│   ├── migrations/                        # placeholder; skema asli di Prisma
│   └── seeders/AkunSeeder.php             # via PrismaClient
├── tests/
│   ├── Feature/                           # PHPUnit
│   └── Unit/Ahp/                          # PHPUnit + property tests via eris
└── prisma-service/                        # Node.js sidecar
    ├── package.json
    ├── prisma/
    │   ├── schema.prisma                  # 10 model
    │   └── migrations/
    ├── src/
    │   ├── server.ts                      # Express bootstrap
    │   ├── auth.ts                        # shared-secret middleware
    │   ├── routes/{akun, supplier, produk, kriteria, subkriteria,
    │   │           penilaian-kriteria, penilaian-subkriteria,
    │   │           penilaian-supplier, hasil-ahp, pengadaan,
    │   │           rekap-supplier}.ts
    │   └── prismaClient.ts
    └── tests/                             # Vitest
```

### Process Lifecycle

- Laravel dijalankan via `php artisan serve` (port 8000).
- Sidecar dijalankan via `npm run start` di `prisma-service/` (port 3001).
- Untuk kemudahan dev disediakan `composer run dev` (concurrently menjalankan `artisan serve` + `npm --prefix prisma-service run dev`) dan dokumentasi di README.
- Health check sidecar: `GET /healthz` mengembalikan `{ ok: true, prisma: 'connected' }`. Laravel memanggil endpoint ini saat boot melalui `PrismaServiceProvider::boot` (lazy, dengan cache 30 detik) sehingga halaman dapat menampilkan banner "Layanan database tidak tersedia" jika sidecar mati.

## Components and Interfaces

### Auth_Module

**Tanggung jawab**: login, logout, session, role guard, CSRF.

**Komponen Laravel**:
- `LoginController@showForm`, `LoginController@login`, `LoginController@logout`.
- `LoginRequest` (FormRequest): validasi `username` (required, string), `password` (required, string).
- `EnsureRole` middleware (didaftarkan dengan alias `role`): `Route::middleware('role:supervisor')` cek `auth()->user()->role === $role`, jika tidak return abort(403).
- Custom `UserProvider` yang mengambil akun dari Prisma_Service dan membuat `Authenticatable` in-memory (lihat Data Models).
- Hash password menggunakan `Hash::make()` (bcrypt, default Laravel) saat seeding atau pembuatan akun.
- Session driver default `file`.

**Routing groups**:

```php
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware('role:supervisor')->prefix('supervisor')
         ->name('supervisor.')->group(/* ... */);

    Route::middleware('role:sales')->prefix('sales')
         ->name('sales.')->group(/* ... */);

    Route::middleware('role:logistik')->prefix('logistik')
         ->name('logistik.')->group(/* ... */);
});
```

Tidak ada route registrasi atau lupa password (memenuhi 1.11, 13.2).

### Master_Module

**Komponen**:
- Controller per entitas: SupplierController, ProdukController, KriteriaController, SubkriteriaController.
- FormRequest per entitas dengan rule wajib (nama, kode, dst).
- Repository di `app/Services/Prisma/Repositories/` membungkus pemanggilan Prisma_Service.
- Untuk Sales/Logistik, hanya route `index`/`show` yang dibuat. Route `store`, `update`, `destroy` ditempatkan di group `role:supervisor`.

**Cascade-deletion guard** (Req 2.6, 3.5): sebelum menghapus, controller memanggil `PengadaanRepository::countBySupplier($id)` dan `PenilaianSupplierRepository::countBySupplier($id)`. Jika > 0, lempar `BusinessRuleException` dengan pesan i18n. Pola yang sama untuk kriteria → cek subkriteria & penilaian_kriteria.

### AHP_Module

**Sub-komponen**:

1. `PairwiseInputController` (per level: Kriteria, Subkriteria, Supplier).
2. `AhpCalculatorService` (pure PHP, tanpa side-effect, mudah di-unit-test).
3. `ConsistencyChecker` (lookup RI table).
4. `RankingService` (orkestrasi: ambil pairwise → kalkulasi → simpan hasil).

**Saaty scale**: nilai valid pairwise = `{1/9, 1/8, ..., 1/2, 1, 2, ..., 9}`. Direpresentasikan sebagai `float` dengan tolerance 1e-6 saat verifikasi.

**RI Table** (Saaty, n=1..10):

```
n  : 1     2     3     4     5     6     7     8     9     10
RI : 0.00  0.00  0.58  0.90  1.12  1.24  1.32  1.41  1.45  1.49
```

#### AhpCalculatorService Pseudocode

```
class AhpCalculatorService:
    RI = [0.00, 0.00, 0.58, 0.90, 1.12, 1.24, 1.32, 1.41, 1.45, 1.49]

    function calculate(matrix A: float[n][n]) -> AhpResult:
        # Pre-condition: A[i][i] = 1, A[i][j] > 0, A[i][j] = 1 / A[j][i]
        n = len(A)
        assert n >= 1 and n <= 10

        # Step 1: column sums
        colSum = array of length n
        for j in 0..n-1:
            colSum[j] = sum(A[i][j] for i in 0..n-1)

        # Step 2: normalized matrix
        N = matrix[n][n]
        for i in 0..n-1:
            for j in 0..n-1:
                N[i][j] = A[i][j] / colSum[j]

        # Step 3: weights = row averages of normalized matrix
        W = array of length n
        for i in 0..n-1:
            W[i] = mean(N[i][j] for j in 0..n-1)

        # Step 4: lambda_max
        # method: for each row i, lambda_i = (A·W)[i] / W[i]; lambda_max = mean(lambda_i)
        AW = array of length n
        for i in 0..n-1:
            AW[i] = sum(A[i][j] * W[j] for j in 0..n-1)

        lambda_i = array of length n
        for i in 0..n-1:
            lambda_i[i] = AW[i] / W[i]

        lambda_max = mean(lambda_i)

        # Step 5: CI, CR
        if n <= 2:
            CI = 0.0
            CR = 0.0
        else:
            CI = (lambda_max - n) / (n - 1)
            CR = CI / RI[n - 1]      # RI is 0-indexed: RI[n-1] for n elements

        consistent = (CR <= 0.10)

        return AhpResult(
            normalized = N,
            weights    = W,
            lambdaMax  = lambda_max,
            CI         = CI,
            CR         = CR,
            consistent = consistent
        )
```

**RankingService** orkestrasi (Req 7.1):

```
function computeRanking(supplierIds, kriteriaIds):
    Wk = AhpCalculatorService.calculate(pairwiseKriteria).weights
    bobotSubPerKriteria = {}
    for k in kriteriaIds:
        bobotSubPerKriteria[k] = AhpCalculatorService
                                 .calculate(pairwiseSubkriteria[k])
                                 .weights

    bobotSupplierPerSubkriteria = {}
    for sub in allSubkriteriaIds:
        bobotSupplierPerSubkriteria[sub] =
            AhpCalculatorService.calculate(pairwiseSupplier[sub]).weights

    nilaiAkhir = array of length len(supplierIds), all zeros
    for kIdx, k in enumerate(kriteriaIds):
        for subIdx, sub in enumerate(subkriteria[k]):
            for sIdx, s in enumerate(supplierIds):
                nilaiAkhir[sIdx] +=
                      Wk[kIdx]
                    * bobotSubPerKriteria[k][subIdx]
                    * bobotSupplierPerSubkriteria[sub][sIdx]

    ranking = sortDescByValue(supplierIds zipped with nilaiAkhir)
    HasilAhpRepository.replaceAll(ranking)
    return ranking
```

#### Reference Panel (Hybrid Pairwise Supplier — Req 5)

Halaman `/supervisor/ahp/supplier/{subkriteriaId}` menampilkan:

- **Card per pasangan supplier** (S_a vs S_b) dengan number selector skala Saaty.
- **Reference Panel** di samping/atas matriks: tabel ringkas tiap supplier yang dibandingkan dengan kolom `Nama`, `Total Persen Cacat`, `Total Persen Keterlambatan`, `Mean Hari Keterlambatan`. Data diambil sekali via `SupplierRepository::listWithRekap($supplierIds)` dan dirender server-side.
- **Tidak ada auto-fill**: nilai pairwise tetap kosong sampai Supervisor memilih. Reference panel adalah informasi pendukung saja. Implementasinya: `<x-ui.card>` dengan komentar Blade `{{-- READ-ONLY REFERENCE; TIDAK MENGISI <input> --}}` dan tidak ada handler JS yang menulis ke input pairwise.

### PO_Module

**Komponen**:
- `PengadaanController` (Sales): create, update, destroy PO.
- `AktualPengadaanController` (Logistik): update kolom `tanggal_kedatangan`, `jumlah_diterima`, `jumlah_cacat`.
- `PengadaanObserver` (lihat di bawah) didaftarkan via `EventServiceProvider`.
- `PhotoUploadService`: simpan ke `storage/app/public/pengadaan/{id}/{uuid}.{ext}`.

**Validasi PO (Req 8.4)**: `PengadaanStoreRequest` requires supplier_id, produk_id, jumlah_dibeli, tanggal_po (semua required, exists/before:tomorrow).

**Validasi data aktual (Req 9.4)**: `AktualPengadaanRequest`:
```php
'jumlah_diterima' => ['required', 'integer', 'min:0', 'lte:jumlah_dibeli'],
'jumlah_cacat'    => ['required', 'integer', 'min:0', 'lte:jumlah_dibeli'],
```
plus rule kustom `'jumlah_cacat' <= 'jumlah_diterima'`.

**Hitung derived field (Req 9.2, 9.3)** pada saat save:

```
persen_kualitas       = (jumlah_diterima - jumlah_cacat) / jumlah_dibeli * 100
hari_keterlambatan    = max(0, dateDiffInDays(tanggal_kedatangan, tanggal_po))
                        # NOTE: jika datang lebih awal, tetap simpan diff (boleh negatif?)
                        # PRD tidak melarang negatif → simpan apa adanya signed.
```

Implementasi: simpan signed (negatif berarti datang lebih awal). Property test 9.3 hanya menjamin selisih hari benar.

#### PengadaanObserver (Req 9.5)

```
class PengadaanObserver:
    constructor(RekapSupplierService rekap)

    saved(Pengadaan p):
        # dipanggil setelah create maupun update
        if p.tanggal_kedatangan IS NOT NULL:
            self.rekap.recalculateForSupplier(p.supplier_id)

    deleted(Pengadaan p):
        self.rekap.recalculateForSupplier(p.supplier_id)


class RekapSupplierService:
    function recalculateForSupplier(supplierId):
        rows = PengadaanRepository.findActualBySupplier(supplierId)
        # rows yang sudah punya tanggal_kedatangan saja

        if rows is empty:
            payload = {
                mean_hari_keterlambatan:    0,
                total_persen_cacat:         0,
                total_persen_keterlambatan: 0,
            }
        else:
            totalDibeli   = sum(r.jumlah_dibeli for r in rows)
            totalCacat    = sum(r.jumlah_cacat  for r in rows)
            totalTerlambat= count(r for r in rows if r.hari_keterlambatan > 0)
            sumHari       = sum(r.hari_keterlambatan for r in rows)

            payload = {
                mean_hari_keterlambatan:    sumHari / count(rows),
                total_persen_cacat:         totalCacat / totalDibeli * 100,
                total_persen_keterlambatan: totalTerlambat / count(rows) * 100,
            }

        SupplierRepository.updateRekap(supplierId, payload)
        # via Prisma_Service; satu round-trip atomik
```

Karena observer Eloquent dipakai hanya sebagai *trigger* (model `Pengadaan` adalah model "shadow" yang tidak mengeksekusi query Eloquent saat persist — controller memanggil PrismaRepository, lalu memanggil `event(new PengadaanSaved($id))` → observer-like listener). Ini menjaga aturan Req 11.1: tidak ada Eloquent query terhadap MySQL untuk operasi domain.

> **Implementasi konkret**: alih-alih memakai Eloquent observer langsung, kami mendaftarkan listener Laravel `PengadaanSaved` / `PengadaanDeleted` Events. Nama "Observer" tetap dipertahankan di kode untuk kejelasan domain (`PengadaanObserver implements ShouldQueue=false`) dan dipanggil eksplisit oleh `PengadaanController` setelah PrismaRepository sukses commit.

### Report_Module

- `LaporanPenilaianController`, `LaporanPengadaanController`, `ProfilSupplierController`.
- Membaca data via Prisma_Service (read-only).
- Visualisasi Chart.js: `<canvas id="chartRanking">` (bar) dan `<canvas id="chartBobotKriteria">` (donut). Dataset dirender ke `data-*` attribute dan di-pickup oleh Alpine `x-data="ahpCharts(...)"`.
- Tombol "Ajukan laporan" hanya muncul untuk Supervisor (`@if(auth()->user()->role === 'supervisor')`).

### PrismaService Component (Laravel side)

`PrismaClient` adalah satu-satunya kelas yang berbicara dengan sidecar. Semua repository memanggilnya.

```php
class PrismaClient {
    public function __construct(
        private \GuzzleHttp\Client $http,
        private string $baseUrl,
        private string $sharedSecret,
    ) {}

    public function call(string $method, string $path, array $body = []): array
    {
        try {
            $response = $this->http->request($method, $this->baseUrl . $path, [
                'headers' => [
                    'X-Prisma-Secret' => $this->sharedSecret,
                    'Accept'          => 'application/json',
                ],
                'json'    => $body,
                'timeout' => 5,
            ]);
            return json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new PrismaException(
                'Layanan database (Prisma) tidak tersedia. Silakan hubungi administrator.',
                previous: $e
            );
        }
    }
}
```

Setiap repository hanya berisi mapping path/method, contoh:

```php
class SupplierRepository {
    public function __construct(private PrismaClient $prisma) {}

    public function all(): array {
        return $this->prisma->call('GET', '/suppliers');
    }

    public function create(array $data): array {
        return $this->prisma->call('POST', '/suppliers', $data);
    }

    public function listWithRekap(array $ids): array {
        return $this->prisma->call('POST', '/suppliers/with-rekap', ['ids' => $ids]);
    }

    public function updateRekap(int $id, array $payload): array {
        return $this->prisma->call('PATCH', "/suppliers/{$id}/rekap", $payload);
    }
}
```

### Prisma_Service (Node.js sidecar)

Express server (TypeScript) yang menerima request Laravel, menjalankan Prisma operasi, dan mengembalikan JSON.

**Authentication**: middleware `requireSecret` membandingkan header `X-Prisma-Secret` dengan env `PRISMA_SHARED_SECRET`. Server hanya bind ke `127.0.0.1`.

**Endpoint contract** (REST, JSON):

| Method | Path | Body | Response |
|---|---|---|---|
| GET | `/healthz` | — | `{ ok, prisma: 'connected' }` |
| GET | `/akun?username=` | — | akun atau 404 |
| POST | `/akun` | `{ username, password_hash, role }` | akun baru |
| GET | `/suppliers` | — | array |
| POST | `/suppliers` | data supplier | supplier baru |
| GET | `/suppliers/:id` | — | supplier + rekap |
| PUT | `/suppliers/:id` | data supplier | supplier ter-update |
| DELETE | `/suppliers/:id` | — | `{ deleted: true }` atau 409 (referenced) |
| POST | `/suppliers/with-rekap` | `{ ids: number[] }` | array supplier dgn rekap |
| PATCH | `/suppliers/:id/rekap` | `{ mean_hari_keterlambatan, total_persen_cacat, total_persen_keterlambatan }` | supplier ter-update |
| GET / POST / PUT / DELETE | `/produk[/:id]` | — | analog supplier |
| GET / POST / PUT / DELETE | `/kriteria[/:id]` | — | analog |
| GET / POST / PUT / DELETE | `/subkriteria[/:id]` | — | analog |
| POST | `/penilaian-kriteria/replace` | `{ matrix: number[][], kriteriaIds: number[] }` | OK |
| POST | `/penilaian-subkriteria/replace` | `{ kriteriaId, matrix, subkriteriaIds }` | OK |
| POST | `/penilaian-supplier/replace` | `{ subkriteriaId, matrix, supplierIds }` | OK |
| GET | `/penilaian-kriteria` | — | matrix terakhir |
| GET | `/penilaian-subkriteria/:kriteriaId` | — | matrix |
| GET | `/penilaian-supplier/:subkriteriaId` | — | matrix |
| POST | `/hasil-ahp/replace` | `{ entries: [{ supplier_id, nilai_akhir, ranking }] }` | OK |
| GET | `/hasil-ahp` | — | array ranking |
| GET / POST / PUT / DELETE | `/pengadaan[/:id]` | — | analog |
| GET | `/pengadaan/by-supplier/:id?actualOnly=1` | — | array |

Semua endpoint write yang melibatkan beberapa tabel (`/penilaian-*/replace`, `/hasil-ahp/replace`, `DELETE /suppliers/:id`) dibungkus `prisma.$transaction(...)` untuk memenuhi Req 11.4 (no partial state).

## Data Models

### Prisma Schema (`prisma-service/prisma/schema.prisma`)

```prisma
generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "mysql"
  url      = env("DATABASE_URL")
}

model Akun {
  id            Int      @id @default(autoincrement())
  username      String   @unique
  password_hash String
  nama          String
  role          Role
  created_at    DateTime @default(now())
  updated_at    DateTime @updatedAt
  @@map("data_akun")
}

enum Role {
  supervisor
  sales
  logistik
}

model Supplier {
  id                          Int       @id @default(autoincrement())
  kode                        String    @unique
  nama                        String
  alamat                      String?
  telepon                     String?
  email                       String?
  mean_hari_keterlambatan     Float     @default(0)
  total_persen_cacat          Float     @default(0)
  total_persen_keterlambatan  Float     @default(0)
  pengadaan                   Pengadaan[]
  penilaian                   PenilaianSupplier[]
  hasil                       HasilAhp[]
  created_at                  DateTime  @default(now())
  updated_at                  DateTime  @updatedAt
  @@map("data_supplier")
}

model Produk {
  id          Int       @id @default(autoincrement())
  kode        String    @unique
  nama        String
  satuan      String
  harga       Decimal   @db.Decimal(15, 2)
  pengadaan   Pengadaan[]
  created_at  DateTime  @default(now())
  updated_at  DateTime  @updatedAt
  @@map("data_produk")
}

model Kriteria {
  id            Int       @id @default(autoincrement())
  kode          String    @unique           // C, Q, D, S, R
  nama          String                       // Cost, Quality, Delivery, Service, Repair Service
  deskripsi     String?
  subkriteria   Subkriteria[]
  pkriteria     PenilaianKriteria[] @relation("PenilaianKriteriaA")
  pkriteriaB    PenilaianKriteria[] @relation("PenilaianKriteriaB")
  created_at    DateTime  @default(now())
  updated_at    DateTime  @updatedAt
  @@map("data_kriteria")
}

model Subkriteria {
  id            Int       @id @default(autoincrement())
  kriteria_id   Int
  kriteria      Kriteria  @relation(fields: [kriteria_id], references: [id])
  kode          String
  nama          String
  deskripsi     String?
  pSubA         PenilaianSubkriteria[] @relation("PenilaianSubkriteriaA")
  pSubB         PenilaianSubkriteria[] @relation("PenilaianSubkriteriaB")
  pSupplier     PenilaianSupplier[]
  created_at    DateTime  @default(now())
  updated_at    DateTime  @updatedAt
  @@map("data_subkriteria")
  @@unique([kriteria_id, kode])
}

model PenilaianKriteria {
  id          Int       @id @default(autoincrement())
  a_id        Int
  b_id        Int
  nilai       Float                          // skala Saaty 1/9..9
  kriteriaA   Kriteria  @relation("PenilaianKriteriaA", fields: [a_id], references: [id])
  kriteriaB   Kriteria  @relation("PenilaianKriteriaB", fields: [b_id], references: [id])
  created_at  DateTime  @default(now())
  @@map("penilaian_kriteria")
  @@unique([a_id, b_id])
}

model PenilaianSubkriteria {
  id              Int           @id @default(autoincrement())
  kriteria_id     Int
  a_id            Int
  b_id            Int
  nilai           Float
  subkriteriaA    Subkriteria   @relation("PenilaianSubkriteriaA", fields: [a_id], references: [id])
  subkriteriaB    Subkriteria   @relation("PenilaianSubkriteriaB", fields: [b_id], references: [id])
  created_at      DateTime      @default(now())
  @@map("penilaian_subkriteria")
  @@unique([kriteria_id, a_id, b_id])
}

model PenilaianSupplier {
  id              Int           @id @default(autoincrement())
  subkriteria_id  Int
  a_supplier_id   Int
  b_supplier_id   Int
  nilai           Float
  subkriteria     Subkriteria   @relation(fields: [subkriteria_id], references: [id])
  supplierA       Supplier      @relation(fields: [a_supplier_id], references: [id], name: "psA")
  supplierB       Supplier      @relation(fields: [b_supplier_id], references: [id], name: "psB")
  created_at      DateTime      @default(now())
  @@map("penilaian_supplier")
  @@unique([subkriteria_id, a_supplier_id, b_supplier_id])
}

model HasilAhp {
  id            Int      @id @default(autoincrement())
  supplier_id   Int
  nilai_akhir   Float
  ranking       Int
  computed_at   DateTime @default(now())
  supplier      Supplier @relation(fields: [supplier_id], references: [id])
  @@map("data_hasil_ahp")
  @@index([ranking])
}

model Pengadaan {
  id                    Int       @id @default(autoincrement())
  supplier_id           Int
  produk_id             Int
  jumlah_dibeli         Int
  tanggal_po            DateTime  @db.Date
  tanggal_kedatangan    DateTime? @db.Date
  jumlah_diterima       Int?
  jumlah_cacat          Int?
  persen_kualitas       Float?
  hari_keterlambatan    Int?
  foto_path             String?
  catatan               String?
  supplier              Supplier  @relation(fields: [supplier_id], references: [id])
  produk                Produk    @relation(fields: [produk_id], references: [id])
  created_at            DateTime  @default(now())
  updated_at            DateTime  @updatedAt
  @@map("data_pengadaan")
  @@index([supplier_id])
  @@index([tanggal_po])
}
```

Relasi `PenilaianKriteria.kriteriaA/kriteriaB`, `PenilaianSubkriteria.subkriteriaA/subkriteriaB`, dan `PenilaianSupplier.supplierA/supplierB` dimodelkan dengan named relations agar Prisma client dapat membedakan dua FK yang menunjuk model yang sama.

### Laravel Authenticatable

Karena akun tidak diakses via Eloquent, kita pakai class POPO yang implement `Illuminate\Contracts\Auth\Authenticatable`:

```php
final class Akun implements Authenticatable {
    public function __construct(
        public int $id,
        public string $username,
        public string $nama,
        public string $role,            // 'supervisor' | 'sales' | 'logistik'
        public string $passwordHash,
    ) {}

    public function getAuthIdentifierName(): string { return 'id'; }
    public function getAuthIdentifier()     : int    { return $this->id; }
    public function getAuthPassword()       : string { return $this->passwordHash; }
    public function getAuthPasswordName()   : string { return 'password_hash'; }
    public function getRememberToken()      : string { return ''; }
    public function setRememberToken($v)    : void   {}
    public function getRememberTokenName()  : string { return ''; }
}
```

Custom `PrismaUserProvider` mengimplement `retrieveById`, `retrieveByCredentials`, `validateCredentials` (memakai `Hash::check`).

## Sequence Diagrams

### Login Flow (Req 1.1–1.4)

```
Browser   Laravel              PrismaUserProvider   PrismaClient   Sidecar    MySQL
  |  POST /login (csrf)  |          |                    |            |        |
  |--------------------> |          |                    |            |        |
  |     LoginRequest validate                            |            |        |
  |     attempt(username,password)                       |            |        |
  |                      |---------> retrieveByCredentials              |        |
  |                      |          |--- GET /akun?username= --------> |        |
  |                      |          |                    |---SELECT--> |
  |                      |          |                    |<--row------ |
  |                      |          |<-- akun (or 404)----              |        |
  |                      | validateCredentials (Hash::check)            |        |
  |                      | session()->put + regenerate                  |        |
  |     redirect /<role>/dashboard                                      |        |
  | <--------------------|                                              |        |
```

### Pairwise Supplier Save + Ranking (Req 5–7)

```
Supervisor  PairwiseSupplierController  AhpCalculator  RankingService  PrismaClient  Sidecar
  | submit pairwise supplier (subId)
  |----------------> validate via PairwiseSupplierRequest
  |                  prisma->/penilaian-supplier/replace ------------> ($transaction)
  |                                                                   <-- OK
  |                  if last subkriteria:
  |                      RankingService.computeRanking()
  |                          AhpCalculator.calculate(...)x N levels
  |                          prisma->/hasil-ahp/replace --------------> ($transaction)
  |                                                                    <-- OK
  | redirect ranking page
```

### Input Aktual + Observer Rekap (Req 9.5)

```
Logistik  AktualPengadaanController  PrismaClient  Sidecar  PengadaanObserver  RekapSupplierService
  | submit form aktual
  |----------------> validate
  |                  prisma->PUT /pengadaan/:id ------> ($tx, hitung %, hari)
  |                                                    <-- ok
  |                  event(PengadaanSaved($id))
  |                  ----------------------------------------> handle
  |                                                              recalcForSupplier(s)
  |                                                              prisma->GET /pengadaan/by-supplier/:id?actualOnly=1
  |                                                              hitung mean/total
  |                                                              prisma->PATCH /suppliers/:id/rekap
  | redirect ke list
```

## Error Handling

### Prisma Sidecar Error Handling (Req 11.4)

| Skenario | Behavior Laravel |
|---|---|
| Sidecar mati / connection refused | `PrismaException` → handler global menampilkan view `errors/prisma-down.blade.php` (banner merah + tombol coba lagi). |
| Timeout (>5 detik) | Sama. |
| Sidecar 4xx (validation, conflict) | Repository melempar `PrismaException` dengan HTTP code dipertahankan. Controller menangkap dan flash error ke session. |
| Sidecar 5xx | Sama dengan timeout. |
| Sidecar mengembalikan response valid tapi sebagian gagal | Tidak boleh terjadi: setiap operasi multi-step di sidecar membungkus dengan `prisma.$transaction`. |

`PrismaServiceProvider` mendaftarkan exception handler:

```php
$exceptions->render(function (PrismaException $e, Request $r) {
    return $r->expectsJson()
        ? response()->json(['message' => $e->getUserMessage()], 503)
        : response()->view('errors.prisma-down', ['msg' => $e->getUserMessage()], 503);
});
```

### Validation Errors

`FormRequest` mengembalikan 422 dengan flash `errors`, di-render oleh `<x-ui.alert variant="error">` di setiap form. Field-level error oleh `<x-ui.input :error="$errors->first('nama')" />`.

### AHP Inconsistency (Req 6.7)

`AhpResult.consistent === false` → controller men-flash `inconsistency_warning` + nilai CR; halaman menampilkan `<x-ui.alert variant="warning">` bersama tombol "Input ulang nilai pairwise". Tombol "Lanjut" disabled (`@disabled(!$result->consistent)`).

### Cascade Delete Conflict (Req 2.6, 3.5)

Sidecar mengembalikan HTTP 409 `{ code: 'REFERENCED', message: 'Supplier dipakai di N pengadaan' }`. Controller menangkap dan flash error.

### File Upload

`PhotoUploadService` validasi mime (jpg/png/webp), max 4 MB. Jika gagal, `BadRequestHttpException` → form re-render dengan error.

## UI/UX Guidelines

### Color Tokens (`tailwind.config.js`)

```js
theme: {
  extend: {
    colors: {
      teal: {
        DEFAULT: '#009688',
        dark:    '#00695C',
        light:   '#E0F2F1',
        50: '#E0F2F1',
        500: '#009688',
        700: '#00695C',
      },
    },
    fontFamily: {
      sans: ['Inter', 'Poppins', 'system-ui', 'sans-serif'],
    },
  },
}
```

### Layout (Req 12.2, 12.3)

- **≥ 1024 px**: sidebar fixed kiri lebar `280px`, navbar sticky atas tinggi `80px`, content area `pl-[280px] pt-[80px]`.
- **< 1024 px (≥ 375 px)**: sidebar menjadi drawer. Tombol hamburger di navbar membuka drawer (Alpine `x-data="{ open: false }"`). Backdrop semi-transparan `bg-teal-dark/40`.
- **Tipografi**: heading `font-semibold text-teal-dark`, body `text-slate-700`.

### Komponen Shadcn UI Port

Semua komponen ditempatkan di `resources/views/components/ui/`:

| Komponen | Penggunaan utama |
|---|---|
| `button.blade.php` | Primary (`bg-teal text-white`), secondary, danger |
| `card.blade.php` | Container default tiap section |
| `input.blade.php` | Form input + error slot |
| `table.blade.php` | Daftar supplier/produk/PO |
| `badge.blade.php` | Status PO (Pending, Konsisten/Inkonsisten, dll) |
| `modal.blade.php` | Konfirmasi delete |
| `dropdown.blade.php` | User menu, action menu di tabel |
| `alert.blade.php` | Flash message + inkonsistensi warning |
| `toast.blade.php` | Sukses CRUD (Alpine + dispatch event) |
| `stepper.blade.php` | Indikator Kriteria→Subkriteria→Supplier→Hasil (Req 4.6) |

### Pairwise Card (Req 12.4)

Bukan tabel matriks penuh. Tiap pasangan dirender sebagai `<x-ui.card>` dengan layout:

```
┌─────────────────────────────────────────────┐
│  [A: nama]   ←  number selector 1..9  →  [B: nama]  │
│                  rounded-full teal                  │
└─────────────────────────────────────────────┘
```

Number selector berbentuk `select` styled atau Alpine custom rounded teal yang menampilkan label `"3 (cukup penting)"` dst. Untuk resiprokal, pengguna men-toggle arah (panah ke A atau B).

### Charts (Chart.js, Req 7.4–7.5)

- `chartRanking` → `bar` horizontal, dataset `nilai_akhir` per supplier, warna teal gradient.
- `chartBobotKriteria` → `doughnut`, dataset bobot Wkriteria, palet teal-dark, teal, teal-light.

### Accessibility

- Kontras teks teal-dark vs putih ≥ 4.5:1 (memenuhi WCAG AA).
- Semua input punya `<label>` terkait via `for=`.
- Tombol icon-only menyertakan `aria-label`.
- Stepper memakai `aria-current="step"` pada langkah aktif.

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Login Redirect Sesuai Role

For any akun valid `(username, password, role)` di mana `role ∈ {supervisor, sales, logistik}`, login dengan kredensial tersebut akan menghasilkan respons redirect ke URL dashboard yang sesuai dengan `role` (`/supervisor/dashboard`, `/sales/dashboard`, atau `/logistik/dashboard`) dan membuat session aktif untuk akun itu.

**Validates: Requirements 1.1, 1.2, 1.3, 1.4**

### Property 2: Password Hash Round-Trip

For any password plaintext `p` (panjang 8..128 karakter), setelah akun dibuat dengan password `p`, kolom `password_hash` di database adalah hash bcrypt yang `Hash::check(p, hash)` mengembalikan `true`, dan plaintext `p` tidak pernah tersimpan di tabel.

**Validates: Requirements 1.6**

### Property 3: Authorization Middleware

For any akun dengan role `R` dan untuk setiap URL terproteksi `U` yang mensyaratkan role `R'`, akses HTTP terhadap `U` mengembalikan status 200/302 jika `R == R'`, status 403 jika `R != R'`, dan status 302 ke `/login` jika tidak ada session.

**Validates: Requirements 1.7, 1.8, 2.7, 8.5, 10.5**

### Property 4: Validator Field Wajib

For any payload `P` untuk endpoint pembuatan supplier, produk, kriteria, subkriteria, atau PO, di mana minimal satu field wajib (sesuai FormRequest masing-masing) bernilai null atau string kosong, validator menolak penyimpanan dan tidak ada record baru di tabel target.

**Validates: Requirements 2.5, 8.4**

### Property 5: Cascade Deletion Guard

For any supplier `s` yang memiliki minimal satu record di `data_pengadaan` atau `penilaian_supplier`, permintaan `DELETE /suppliers/:id` mengembalikan 409 dan record `s` tetap ada. Untuk kriteria `k` yang memiliki minimal satu subkriteria atau record `penilaian_kriteria`, `DELETE /kriteria/:id` mengembalikan 409 dan record `k` tetap ada.

**Validates: Requirements 2.6, 3.5**

### Property 6: Saaty Scale Validator

For any nilai `v` yang di-submit ke endpoint pairwise (kriteria, subkriteria, atau supplier), validator menerima `v` jika dan hanya jika `v ∈ { 1/9, 1/8, ..., 1/2, 1, 2, ..., 9 }` (dengan tolerance numerik 1e-6).

**Validates: Requirements 4.2, 5.3**

### Property 7: Pairwise Persistence Round-Trip

For any matriks pairwise valid `M` (n×n, `M[i][i] = 1`, `M[i][j] > 0`, `M[i][j] = 1/M[j][i]`, semua pasangan unik upper-triangle terisi), setelah disimpan via `POST /penilaian-*/replace`, query `GET /penilaian-*` mengembalikan matriks `M'` yang ekuivalen secara numerik dengan `M` (max-error per cell ≤ 1e-9).

**Validates: Requirements 4.3, 4.4, 4.5, 5.4**

### Property 8: Hybrid Invariant — Bobot Supplier Independen Rekap Aktual

For any pasangan state sistem `S1` dan `S2` yang identik dalam seluruh isi tabel `penilaian_supplier`, `penilaian_subkriteria`, `penilaian_kriteria`, dan struktur supplier, namun berbeda nilai pada kolom `mean_hari_keterlambatan`, `total_persen_cacat`, atau `total_persen_keterlambatan` di `data_supplier`, hasil `RankingService::computeRanking()` di `S1` dan `S2` adalah identik (urutan dan `nilai_akhir` sama untuk semua supplier).

**Validates: Requirements 5.5**

### Property 9: AHP Normalisasi dan Bobot

For any matriks pairwise valid `A` berukuran n×n (1 ≤ n ≤ 10), hasil `AhpCalculatorService::calculate(A)` memenuhi: setiap kolom dari matriks ternormalisasi `N` berjumlah `1.0` (tolerance 1e-9), `Σ W[i] = 1.0` (tolerance 1e-9), dan `0 ≤ W[i] ≤ 1` untuk semua `i`.

**Validates: Requirements 6.1, 6.2**

### Property 10: AHP Konsistensi Sempurna

For any vektor bobot positif `w = [w_1, ..., w_n]` dengan `Σ w_i = 1`, jika matriks pairwise dibangun sebagai `A[i][j] = w_i / w_j`, maka hasil `AhpCalculatorService::calculate(A)` memenuhi `λ_max ≈ n` (tolerance 1e-6), `CI ≈ 0` (tolerance 1e-6), `CR ≈ 0` (tolerance 1e-6), dan `consistent == true`.

**Validates: Requirements 6.3, 6.4, 6.5**

### Property 11: CR Threshold 0.10

For any matriks pairwise valid `A` (n ≥ 3), output `consistent` dari `AhpCalculatorService::calculate(A)` bernilai `true` jika dan hanya jika `CR ≤ 0.10`. Untuk `consistent == false`, controller AHP_Module menolak transisi ke langkah berikutnya dan menampilkan peringatan inkonsistensi.

**Validates: Requirements 6.6, 6.7**

### Property 12: Nilai Akhir Invariant

For any kombinasi bobot kriteria `Wk` (Σ = 1), bobot subkriteria `Wsk_per_k` (Σ = 1 per kriteria), dan bobot supplier `Ws_per_sub` (Σ = 1 per subkriteria), hasil `nilai_akhir` per supplier yang dihitung `RankingService::computeRanking()` memenuhi `0 ≤ nilai_akhir[s] ≤ 1` untuk semua supplier `s`, dan `Σ_s nilai_akhir[s] = 1` (tolerance 1e-9).

**Validates: Requirements 7.1**

### Property 13: Ranking Monotonic Descending

For any keluaran `ranking` dari `RankingService::computeRanking()`, untuk semua pasangan posisi `(i, j)` dengan `i < j`, berlaku `ranking[i].nilai_akhir ≥ ranking[j].nilai_akhir`. Posisi pertama (`ranking[0]`) memiliki `nilai_akhir` maksimum di antara seluruh supplier.

**Validates: Requirements 7.3**

### Property 14: Persen Kualitas Formula dan Range

For any triplet `(jumlah_dibeli, jumlah_diterima, jumlah_cacat)` di mana `jumlah_dibeli > 0`, `0 ≤ jumlah_cacat ≤ jumlah_diterima ≤ jumlah_dibeli`, kolom `persen_kualitas` yang dihitung memenuhi `persen_kualitas = (jumlah_diterima - jumlah_cacat) / jumlah_dibeli × 100` dan `0 ≤ persen_kualitas ≤ 100`.

**Validates: Requirements 9.2**

### Property 15: Hari Keterlambatan Formula

For any pasangan tanggal `(tanggal_po, tanggal_kedatangan)`, kolom `hari_keterlambatan` yang dihitung memenuhi `hari_keterlambatan = floor((tanggal_kedatangan - tanggal_po) / 1 day)` dalam satuan hari kalender.

**Validates: Requirements 9.3**

### Property 16: Validator Aktual Quantity

For any input form aktual dengan `(jumlah_dibeli, jumlah_diterima, jumlah_cacat)`, validator menerima input jika dan hanya jika `0 ≤ jumlah_diterima ≤ jumlah_dibeli` dan `0 ≤ jumlah_cacat ≤ jumlah_diterima` (dan dengan demikian `≤ jumlah_dibeli`). Selain itu, validator menolak penyimpanan dan kolom turunan tidak diperbarui.

**Validates: Requirements 9.4**

### Property 17: Observer Rekap Konsisten dengan Agregasi PO

For any urutan operasi (insert/update/delete) terhadap `data_pengadaan` untuk supplier `s`, setelah `PengadaanObserver` selesai berjalan, kolom rekap pada `data_supplier[s]` memenuhi:
- `mean_hari_keterlambatan = mean(hari_keterlambatan)` untuk semua PO `s` yang memiliki `tanggal_kedatangan`
- `total_persen_cacat = Σ jumlah_cacat / Σ jumlah_dibeli × 100` untuk PO aktual `s`
- `total_persen_keterlambatan = count(hari_keterlambatan > 0) / count(PO aktual s) × 100`

(jika tidak ada PO aktual, ketiganya bernilai 0). Jaminan ini bersifat eventual: ditegakkan setelah event handler dari operasi terakhir selesai.

**Validates: Requirements 9.5**

### Property 18: Atomicity Prisma Failure

For any operasi multi-step yang melibatkan beberapa baris (`POST /penilaian-*/replace`, `POST /hasil-ahp/replace`, `DELETE /suppliers/:id` dengan referensi cascade), jika sidecar mengalami kegagalan di tengah eksekusi, state database setelah operasi sama dengan state sebelum operasi dimulai (no partial write). Laravel menerima `PrismaException` dan menampilkan pesan error kepada pengguna tanpa mengubah data domain lain.

**Validates: Requirements 11.4**

## Testing Strategy

### Pendekatan Dual

Setiap requirement testable dijamin oleh dua jenis test komplementer:

- **Property tests** (PHPUnit + `eris/eris` untuk PHP, Vitest + `fast-check` untuk Node) menjamin universal property dengan minimal 100 iterasi per property.
- **Example/integration tests** menutup skenario spesifik, edge case, dan UI presence yang tidak cocok dengan PBT.

### Layer-by-Layer

| Layer | Tools | Fokus |
|---|---|---|
| `AhpCalculatorService` (pure) | PHPUnit + eris | Property 9, 10, 11 (matriks generator, matriks konsisten dari vektor bobot, perturbation untuk inkonsisten) |
| `RankingService` | PHPUnit | Property 8, 12, 13 (mock repository) |
| `RekapSupplierService` + observer | PHPUnit | Property 17 (model based: rekap actual = recompute langsung dari list PO) |
| FormRequest validator | PHPUnit | Property 4, 6, 16 |
| Auth flow | PHPUnit Feature | Property 1, 2, 3 |
| Repository ↔ Sidecar | PHPUnit Feature dengan sidecar mock (Guzzle MockHandler) | Property 7, 18 |
| Prisma sidecar handlers | Vitest | endpoint contract, transaksi atomik (Property 18) |
| UI (charts, stepper, drawer) | PHPUnit Feature + Dusk (opsional) | Example: render canvas, stepper aria-current, drawer toggle |

### Konvensi Tag Property Test

Setiap test PBT diberi anotasi:
```
@group property
@testdox Feature: spk-supplier-ahp, Property {N}: {teks property}
```

Contoh:
```php
/**
 * @group property
 * @testdox Feature: spk-supplier-ahp, Property 9: AHP normalization yields
 *          column-stochastic matrix and weights summing to 1
 */
public function test_normalization_invariants(): void { ... }
```

### Generator Reusable

- `pairwiseMatrixGen(n)` → menghasilkan matriks valid (Saaty values dengan reciprocal di-mirror).
- `consistentMatrixGen(n)` → generate vektor bobot positif, normalisasi, lalu bangun `A[i][j] = w_i/w_j`.
- `validQuantityGen()` → triplet `(dibeli, diterima, cacat)` dengan invariants.
- `dateRangeGen()` → pasangan tanggal di rentang ±2 tahun.
- `roleGen()` ∈ `{supervisor, sales, logistik}`.

### Performance Testing (non-PBT)

- Benchmark `AhpCalculatorService::calculate(10×10)` 100 kali, assert p95 < 200 ms (memenuhi Req 6.8: < 2 s).
- Benchmark single Prisma round-trip (insert supplier) 50 kali, assert p95 < 500 ms (memenuhi Req 11.3).

### Smoke / Integration

- Migration + seed → cek 5 kriteria default ada (Req 3.1), 10 model di Prisma Studio (Req 11.2).
- `GET /healthz` di sidecar → ok.
- Render `/login` tidak butuh auth; `/supervisor/dashboard` tanpa auth → redirect `/login`.
- Browser smoke (manual) Chrome/Firefox/Edge versi terbaru-2 (Req 12.5).

### CI Catatan

- PHPUnit + eris dijalankan di Windows/Laragon/XAMPP environment dev. Tidak ada CI cloud (sesuai cakupan localhost).
- Sidecar Vitest dijalankan via `npm --prefix prisma-service test`.
