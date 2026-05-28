<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Pengadaan;
use App\Models\PenilaianSupplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('supervisor.supplier.index', compact('suppliers'));
    }

    public function create()
    {
        return view('supervisor.supplier.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:data_supplier,kode',
            'nama' => 'required|string|max:120',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        Supplier::create($validated);

        return redirect()->route('supervisor.supplier.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('supervisor.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:data_supplier,kode,' . $supplier->id,
            'nama' => 'required|string|max:120',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $supplier->update($validated);

        return redirect()->route('supervisor.supplier.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        // Cascade-deletion guard (Req 2.6)
        $hasPo = Pengadaan::where('supplier_id', $supplier->id)->exists();
        $hasPenilaian = PenilaianSupplier::where('a_supplier_id', $supplier->id)
            ->orWhere('b_supplier_id', $supplier->id)
            ->exists();

        if ($hasPo || $hasPenilaian) {
            return redirect()->route('supervisor.supplier.index')
                ->with('error', 'Supplier "' . $supplier->nama . '" tidak dapat dihapus karena masih digunakan dalam data pengadaan atau penilaian.');
        }

        $supplier->delete();

        return redirect()->route('supervisor.supplier.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
