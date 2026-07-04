# AHP Consistency Advisor Implementation

## Overview
Implementasi sistem rekomendasi matematis untuk memperbaiki inkonsistensi matriks AHP tanpa menggunakan AI. Sistem ini memblok submission yang tidak konsisten (CR > 0.1) dan memberikan saran perbaikan yang konkret.

## Fitur yang Diimplementasikan

### 1. **ConsistencyAdvisor Service** (`app/Services/Ahp/ConsistencyAdvisor.php`)
Service baru yang menganalisis inkonsistensi matriks AHP dan memberikan rekomendasi perbaikan.

#### Metode Utama:

**`analyze(array $matrix, array $weights, array $items): array`**
- Menganalisis inkonsistensi dengan membandingkan matriks user dengan matriks ideal
- Menghitung gap dan percentage difference untuk setiap pasangan
- Mengurutkan berdasarkan prioritas (high, medium, low)
- Memberikan rekomendasi nilai yang disarankan
- Limit: top 5 suggestions untuk menghindari information overload

**Cara Kerja Algoritma:**
```
1. Hitung matriks ideal dari bobot (wi/wj)
2. Bandingkan setiap pasangan: user_value vs ideal_value
3. Hitung gap = |user_value - ideal_value|
4. Tentukan prioritas berdasarkan gap dan percentage difference:
   - High: gap > 2.5 ATAU percent_diff > 60%
   - Medium: gap > 1.5 ATAU percent_diff > 40%
   - Low: sisanya
5. Bulatkan nilai yang disarankan ke skala AHP (1-9)
```

**`roundToAhpScale(float $value): float`**
- Membulatkan nilai ke skala AHP yang valid (1, 2, 3, ..., 9)
- Handle reciprocal values (<1) dengan tepat

**`generateExplanation()`**
- Memberikan penjelasan human-readable dalam Bahasa Indonesia
- Menjelaskan apakah nilai terlalu besar atau terlalu kecil

**`estimateCrAfterChange()`**
- Estimasi CR baru jika saran diterapkan
- Untuk preview "what-if" analysis

**`findMostInconsistentPair()`**
- Identifikasi single pair yang paling berkontribusi pada inkonsistensi
- Menggunakan squared difference untuk emphasis

#### Output Format:
```php
[
    'pair' => [
        'i' => 0,
        'j' => 1,
        'name_i' => 'Harga',
        'name_j' => 'Kualitas',
    ],
    'current' => 5.0,
    'suggested' => 3.0,
    'ideal' => 2.83,
    'gap' => 2.17,
    'percent_diff' => 76.7,
    'priority' => 'high',
    'explanation' => 'Nilai terlalu besar. Penilaian Harga vs Kualitas tidak sesuai...'
]
```

---

### 2. **Update ConsistencyChecker** (`app/Services/Ahp/ConsistencyChecker.php`)
Ditambahkan dependency injection `ConsistencyAdvisor` dan method baru:

**`analyzeWithSuggestions(array $matrix, AhpResult $result, array $items): array`**
- Wrapper method yang menggabungkan consistency check dengan suggestions
- Return comprehensive analysis data

---

### 3. **Update AhpController** (`app/Http/Controllers/Supervisor/AhpController.php`)

#### Constructor Changes:
```php
public function __construct(
    AhpCalculatorService $calculator, 
    RankingService $rankingService,
    ConsistencyAdvisor $advisor
)
```

#### `kriteriaForm()` Method:
- Menambahkan variabel `$suggestions = []`
- Jika tidak konsisten, generate suggestions secara real-time
- Pass `$suggestions` ke view

#### `kriteriaSave()` Method:
- **BLOK submission jika CR > 0.1** (strict mode)
- Generate suggestions saat error
- Flash `suggestions` dan `cr_value` ke session untuk ditampilkan di redirect
- Return dengan error message + suggestions

```php
if (!$result->consistent) {
    $kriteriaNames = $kriterias->pluck('nama')->toArray();
    $suggestions = $this->advisor->analyze($matrix, $result->weights, $kriteriaNames);
    
    return redirect()->route('supervisor.ahp.kriteria')
        ->with('error', 'Matriks perbandingan kriteria TIDAK KONSISTEN...')
        ->with('suggestions', $suggestions)
        ->with('cr_value', $result->CR);
}
```

---

### 4. **Update View Kriteria** (`resources/views/supervisor/ahp/kriteria.blade.php`)

#### Error Alert Section (Top of Page):
Ditambahkan alert box merah di atas form yang muncul setelah save gagal:
- Menampilkan error message
- Menampilkan suggestions dari flash session
- Priority badges (🔴 High, 🟡 Medium, 🟢 Low)
- Current value → Suggested value dengan arrow
- Explanation text
- Info box dengan catatan cara penggunaan

#### Sidebar Suggestions Box:
Real-time suggestions yang muncul saat user mengisi form:
- Muncul di sidebar sebelah kanan
- Sama dengan format error alert
- Menggunakan variabel `$suggestions` dari controller
- Hanya muncul jika `!empty($suggestions)`

#### Visual Design:
- Menggunakan color coding:
  - **Red** (High priority): border-red-200, bg-red-50
  - **Amber** (Medium priority): border-amber-200, bg-amber-50
  - **Slate** (Low priority): border-slate-200, bg-slate-50
- Icons: Font Awesome
- Typography: Tailwind utility classes
- Responsive layout

---

## User Flow

### Scenario 1: Form Input (Real-time)
```
1. User mengisi form perbandingan kriteria
2. User scroll ke bawah melihat "Status Konsistensi"
3. Jika CR > 0.1, sidebar menampilkan:
   ❌ Status: Tidak Konsisten (CR = 0.2859)
   💡 Saran Perbaikan (muncul di bawahnya)
4. User membaca suggestions
5. User scroll ke atas untuk mengubah nilai
6. User klik "Simpan & Lanjutkan"
```

### Scenario 2: After Submit Error (Redirect)
```
1. User klik "Simpan & Lanjutkan"
2. Backend validate: CR > 0.1 → REJECT
3. Redirect ke kriteria form dengan:
   - Flash error message
   - Flash suggestions
4. Page reload, error alert muncul di atas:
   🔴 Matriks Tidak Konsisten
   💡 Saran Perbaikan (dalam box)
5. User membaca dan apply suggestions
6. User klik "Simpan & Lanjutkan" lagi
7. Jika masih gagal, ulangi
8. Jika berhasil (CR ≤ 0.1), lanjut ke subkriteria
```

---

## Technical Details

### Dependency Injection
Laravel's service container automatically resolves:
```php
ConsistencyAdvisor → injected to AhpController
```

### Session Flash Data
```php
->with('error', $message)
->with('suggestions', $array)
->with('cr_value', $float)
```

Accessed in Blade:
```blade
@if(session('suggestions'))
    {{ session('suggestions') }}
@endif
```

### Blade Conditional Rendering
```blade
{{-- Real-time suggestions in sidebar --}}
@if(!empty($suggestions) && count($suggestions) > 0)
    <!-- Show suggestions box -->
@endif

{{-- After-submit suggestions at top --}}
@if(session('suggestions') && count(session('suggestions')) > 0)
    <!-- Show error alert with suggestions -->
@endif
```

---

## Mathematical Foundation

### Consistency Ratio (CR)
```
CR = CI / RI
CI = (λmax - n) / (n - 1)
λmax = average of (Matrix × Weights) / Weights
```

### Ideal Matrix Calculation
```
ideal[i][j] = weights[i] / weights[j]
```

**Contoh:**
```
Jika weights = [0.65, 0.23, 0.12]
Maka ideal[0][1] = 0.65 / 0.23 = 2.83
Artinya: Kriteria 0 seharusnya 2.83x lebih penting dari Kriteria 1
```

### Gap Analysis
```
gap = |user_value - ideal_value|
percent_diff = (gap / ideal_value) × 100%
```

### Priority Determination
```
if gap > 2.5 OR percent_diff > 60% → HIGH
else if gap > 1.5 OR percent_diff > 40% → MEDIUM
else → LOW
```

---

## Extension Points

### Future Enhancements (Not Yet Implemented):

1. **Auto-Apply Suggestions**
   - Button "Terapkan Semua Saran" untuk auto-fill form
   - AJAX update tanpa refresh page

2. **Preview CR Estimation**
   - Tampilkan estimasi CR baru jika saran diterapkan
   - "Jika Anda ubah X→Y, CR akan turun ke ~0.09"

3. **Interactive Highlighting**
   - Highlight form input yang perlu diubah
   - Scroll otomatis ke pasangan yang bermasalah

4. **Multi-Step Guidance**
   - "Ubah saran #1 dulu, simpan, lalu lihat apakah perlu saran lainnya"
   - Progressive refinement approach

5. **History & Analytics**
   - Track berapa kali user harus revisi sebelum konsisten
   - Pattern analysis untuk training

6. **Subkriteria & Supplier**
   - Apply logic yang sama ke `subkriteriaForm/Save` dan `supplierForm/Save`
   - Reusable component untuk semua matrix comparisons

---

## Testing Checklist

### Manual Testing:
- [ ] Buat penilaian kriteria yang TIDAK konsisten (CR > 0.1)
- [ ] Verify sidebar menampilkan suggestions
- [ ] Klik "Simpan & Lanjutkan"
- [ ] Verify redirect dengan error + suggestions di atas form
- [ ] Apply saran perbaikan
- [ ] Verify bisa save jika CR ≤ 0.1
- [ ] Test dengan 3 kriteria (minimal case)
- [ ] Test dengan 5+ kriteria (complex case)
- [ ] Test priority levels (high, medium, low)
- [ ] Test UI responsiveness (mobile vs desktop)

### Edge Cases:
- [ ] Matrix dengan n=2 (always consistent)
- [ ] Matrix dengan n=1 (single item)
- [ ] Semua nilai sama (trivial case)
- [ ] Nilai ekstrim (9 vs 1/9)
- [ ] Reciprocal values handling

---

## Deployment Notes

1. **No database changes** required
2. **No environment variables** needed
3. **Clear cache** after deployment:
   ```bash
   php artisan config:clear
   php artisan view:clear
   ```
4. **No breaking changes** - existing functionality preserved

---

## Design Decision: Block vs Warning

**Chosen: BLOCK (Strict Mode)**

**Rationale:**
1. ✅ AHP methodology requires CR ≤ 0.1 for valid results
2. ✅ Business decisions based on invalid data → costly mistakes
3. ✅ Forces users to provide consistent judgments
4. ✅ Educational: teaches proper AHP usage

**Alternative (Warning Mode):**
- Allow submit dengan warning
- Pros: More flexible for experimentation
- Cons: Risk of invalid business decisions
- **Not implemented** - can be added later with feature flag if needed

---

## Code Quality

### Principles Applied:
- ✅ Single Responsibility: Each method does one thing
- ✅ Dependency Injection: Testable, maintainable
- ✅ Type Hints: PHP 8+ strict types
- ✅ Descriptive Naming: Self-documenting code
- ✅ Comments: Explain "why", not "what"
- ✅ DRY: Reusable methods

### Performance:
- O(n²) complexity for matrix comparison - acceptable for small n (< 10 criteria)
- No database queries in advisor - pure computation
- Lightweight frontend - no heavy JS libraries needed

---

## Related Files

### Created:
- `app/Services/Ahp/ConsistencyAdvisor.php`

### Modified:
- `app/Services/Ahp/ConsistencyChecker.php`
- `app/Http/Controllers/Supervisor/AhpController.php`
- `resources/views/supervisor/ahp/kriteria.blade.php`

### Documentation:
- `docs/CONSISTENCY_ADVISOR_IMPLEMENTATION.md` (this file)

---

## Conclusion

Implementasi ini memberikan **mathematical guidance tanpa AI** untuk membantu user memperbaiki inkonsistensi AtrMatriks AHP. System ini:
- ✅ Matematically sound (based on AHP theory)
- ✅ User-friendly (clear visual design)
- ✅ Educational (teaches AHP consistency)
- ✅ Scalable (works for any matrix size)
- ✅ No external dependencies (pure PHP/Blade)

**Next Steps:**
- Apply similar logic to subkriteria and supplier comparisons
- Add unit tests for ConsistencyAdvisor
- Consider adding "auto-apply" feature
- Monitor user feedback for UX improvements
