<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisor\ProdukRequest;
use App\Models\PengadaanDetail;
use App\Models\Produk;
use App\Models\Supplier;
use App\Support\NameSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    /**
     * Display a listing of products with optional name search.
     * Requirements: 2.6, 2.7
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $query = Produk::with('supplier');
        NameSearch::filter($query, 'nama', $search);
        $produks = $query->get();

        return view('supervisor.produk.index', compact('produks', 'search'));
    }

    /**
     * Show the form for creating a new product.
     * Requirements: 2.1, 2.2
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('nama')->get();

        return view('supervisor.produk.create', compact('suppliers'));
    }

    /**
     * Store a newly created product.
     * Requirements: 2.1, 2.3, 2.4, 2.5
     */
    public function store(ProdukRequest $request)
    {
        $data = $request->validated();
        
        // Auto-generate kode if not provided
        if (empty($data['kode'])) {
            // Generate new code based on highest existing
            $lastProduk = Produk::where('kode', 'LIKE', 'P%')
                ->orderByRaw('CAST(SUBSTRING(kode, 2) AS UNSIGNED) DESC')
                ->first();
            
            $nextNumber = 1;
            if ($lastProduk && preg_match('/P(\d+)/', $lastProduk->kode, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }
            
            // Generate unique code, with retry logic in case of collision
            do {
                $data['kode'] = 'P' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                $exists = Produk::where('kode', $data['kode'])->exists();
                if ($exists) {
                    $nextNumber++;
                }
            } while ($exists);
        }
        
        // Combine dimensions if provided separately (order: Lebar x Panjang x Tinggi for paper industry)
        if (!empty($data['lebar']) || !empty($data['panjang']) || !empty($data['tinggi'])) {
            $dimensions = [];
            if (!empty($data['lebar'])) $dimensions[] = $data['lebar'];
            if (!empty($data['panjang'])) $dimensions[] = $data['panjang'];
            if (!empty($data['tinggi'])) $dimensions[] = $data['tinggi'];
            $data['ukuran'] = implode(' × ', $dimensions);
        }
        
        // Combine capacity with unit
        if (!empty($data['kapasitas_nilai']) && !empty($data['kapasitas_satuan'])) {
            $data['kapasitas_pasokan'] = $data['kapasitas_nilai'] . ' ' . $data['kapasitas_satuan'];
        }
        
        // Remove temporary fields
        unset($data['panjang'], $data['lebar'], $data['tinggi'], $data['kapasitas_nilai'], $data['kapasitas_satuan']);

        Produk::create($data);

        return redirect()->route('supervisor.produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Show the form for editing an existing product.
     * Requirements: 2.5
     */
    public function edit(Produk $produk)
    {
        $suppliers = Supplier::orderBy('nama')->get();

        return view('supervisor.produk.edit', compact('produk', 'suppliers'));
    }

    /**
     * Update the specified product.
     * Requirements: 2.5
     */
    public function update(ProdukRequest $request, Produk $produk)
    {
        $data = $request->validated();
        
        // Auto-generate kode if not provided (keep existing code if already set)
        if (empty($data['kode'])) {
            if (empty($produk->kode)) {
                // Generate new code based on highest existing
                $lastProduk = Produk::where('kode', 'LIKE', 'P%')
                    ->orderByRaw('CAST(SUBSTRING(kode, 2) AS UNSIGNED) DESC')
                    ->first();
                
                $nextNumber = 1;
                if ($lastProduk && preg_match('/P(\d+)/', $lastProduk->kode, $matches)) {
                    $nextNumber = intval($matches[1]) + 1;
                }
                
                // Generate unique code, with retry logic in case of collision
                do {
                    $data['kode'] = 'P' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                    $exists = Produk::where('kode', $data['kode'])
                        ->where('id', '!=', $produk->id)
                        ->exists();
                    if ($exists) {
                        $nextNumber++;
                    }
                } while ($exists);
            } else {
                // Keep existing code
                $data['kode'] = $produk->kode;
            }
        }
        
        // Combine dimensions if provided separately (order: Lebar x Panjang x Tinggi for paper industry)
        if (!empty($data['lebar']) || !empty($data['panjang']) || !empty($data['tinggi'])) {
            $dimensions = [];
            if (!empty($data['lebar'])) $dimensions[] = $data['lebar'];
            if (!empty($data['panjang'])) $dimensions[] = $data['panjang'];
            if (!empty($data['tinggi'])) $dimensions[] = $data['tinggi'];
            $data['ukuran'] = implode(' × ', $dimensions);
        }
        
        // Combine capacity with unit
        if (!empty($data['kapasitas_nilai']) && !empty($data['kapasitas_satuan'])) {
            $data['kapasitas_pasokan'] = $data['kapasitas_nilai'] . ' ' . $data['kapasitas_satuan'];
        }
        
        // Remove temporary fields
        unset($data['panjang'], $data['lebar'], $data['tinggi'], $data['kapasitas_nilai'], $data['kapasitas_satuan']);

        $produk->update($data);

        return redirect()->route('supervisor.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Delete the specified product and recalculate affected supplier metrics.
     * 
     * When a product is deleted:
     * 1. Database automatically sets produk_id to NULL in data_pengadaan and data_pengadaan_detail (SET NULL constraint)
     * 2. Performance metrics are recalculated for all affected suppliers
     * 3. Historical procurement records are preserved with NULL produk_id
     * 
     * Requirements: 2.8
     */
    public function destroy(Produk $produk)
    {
        DB::beginTransaction();

        try {
            // Collect all supplier IDs that will be affected by this deletion
            $affectedSupplierIds = collect();

            // 1. Get suppliers from data_pengadaan that reference this product
            $pengadaanSuppliers = \App\Models\Pengadaan::where('produk_id', $produk->id)
                ->whereNotNull('supplier_id')
                ->distinct()
                ->pluck('supplier_id');
            $affectedSupplierIds = $affectedSupplierIds->merge($pengadaanSuppliers);

            // 2. Get suppliers from data_pengadaan_detail via header that reference this product
            $detailSuppliers = PengadaanDetail::where('produk_id', $produk->id)
                ->whereNotNull('pengadaan_id')
                ->with('header:id,supplier_id')
                ->get()
                ->pluck('header.supplier_id')
                ->filter();
            $affectedSupplierIds = $affectedSupplierIds->merge($detailSuppliers);

            // 3. Add the product's own supplier if it exists
            if ($produk->supplier_id) {
                $affectedSupplierIds->push($produk->supplier_id);
            }

            // Remove duplicates and filter out nulls
            $affectedSupplierIds = $affectedSupplierIds->unique()->filter()->values();

            // Delete the product (database will automatically SET NULL in related tables via foreign key constraint)
            $produk->delete();

            // Recalculate metrics for all affected suppliers
            // This ensures supplier performance data reflects only existing products
            if ($affectedSupplierIds->isNotEmpty()) {
                $metricsService = app(\App\Services\Supplier\SupplierMetricsService::class);
                
                foreach ($affectedSupplierIds as $supplierId) {
                    try {
                        $metricsService->recalculateForSupplier($supplierId);
                        \Log::info("Recalculated metrics for supplier ID: {$supplierId}");
                    } catch (\Exception $e) {
                        \Log::error("Failed to recalculate metrics for supplier {$supplierId}: " . $e->getMessage());
                        // Continue with other suppliers even if one fails
                    }
                }
            }

            DB::commit();

            $supplierCount = $affectedSupplierIds->count();
            $message = $supplierCount > 0 
                ? "Produk berhasil dihapus dan kinerja {$supplierCount} supplier telah diperbarui."
                : "Produk berhasil dihapus.";

            return redirect()->route('supervisor.produk.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Failed to delete product {$produk->id}: " . $e->getMessage());

            return redirect()->route('supervisor.produk.index')
                ->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }
}
