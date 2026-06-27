<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisor\SupplierRequest;
use App\Models\PengadaanHeader;
use App\Models\PenilaianSupplier;
use App\Models\Supplier;
use App\Support\NameSearch;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display the supplier list with optional name search.
     * Validates: Requirements 1.5, 1.6, 1.7
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $query = Supplier::query();
        NameSearch::filter($query, 'nama', $search);

        $suppliers = $query->get();

        return view('supervisor.supplier.index', compact('suppliers', 'search'));
    }

    /**
     * Show the supplier creation form.
     * Validates: Requirement 1.1
     */
    public function create()
    {
        return view('supervisor.supplier.create');
    }

    /**
     * Persist a new supplier record.
     * Validates: Requirements 1.1, 1.2, 1.3
     */
    public function store(SupplierRequest $request)
    {
        Supplier::create($request->validated());

        return redirect()->route('supervisor.supplier.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    /**
     * Show the supplier edit form.
     * Validates: Requirement 1.4
     */
    public function edit(Supplier $supplier)
    {
        return view('supervisor.supplier.edit', compact('supplier'));
    }

    /**
     * Persist updates to an existing supplier record.
     * Validates: Requirement 1.4
     */
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return redirect()->route('supervisor.supplier.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    /**
     * Delete a supplier after verifying it is not referenced elsewhere.
     * Validates: Requirement 1.8
     */
    public function destroy(Supplier $supplier)
    {
        // Check if supplier is used in products
        $usedByProduk = $supplier->produk()->exists();

        $usedByPo = PengadaanHeader::where('supplier_id', $supplier->id)->exists();

        $usedByPenilaian = PenilaianSupplier::where('a_supplier_id', $supplier->id)
            ->orWhere('b_supplier_id', $supplier->id)
            ->exists();

        if ($usedByProduk || $usedByPo || $usedByPenilaian) {
            return redirect()->back()
                ->with('error', 'Supplier tidak dapat dihapus karena masih digunakan.');
        }

        $supplier->delete();

        return redirect()->route('supervisor.supplier.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
