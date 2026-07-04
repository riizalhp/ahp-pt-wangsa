<?php

namespace App\Http\Controllers\AdminPurchasing;

use App\Http\Controllers\Controller;
use App\Models\Pengadaan;
use App\Models\Supplier;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengadaanController extends Controller
{
    public function index()
    {
        $pengadaans = Pengadaan::with(['supplier', 'produk'])
            ->orderBy('id', 'desc')
            ->get();
        return view('admin_purchasing.pengadaan.index', compact('pengadaans'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $produks = Produk::all();
        return view('admin_purchasing.pengadaan.create', compact('suppliers', 'produks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:data_supplier,id',
            'produk_id' => 'required|exists:data_produk,id',
            'jumlah_dibeli' => 'required|integer|min:1',
            'tanggal_po' => 'required|date|before_or_equal:today',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'catatan' => 'nullable|string',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            // Store in storage/app/public/pengadaan
            $path = $file->storeAs('public/pengadaan', $filename);
            // We save public path: /storage/pengadaan/filename
            $fotoPath = 'storage/pengadaan/' . $filename;
        }

        $pengadaan = Pengadaan::create([
            'supplier_id' => $validated['supplier_id'],
            'produk_id' => $validated['produk_id'],
            'jumlah_dibeli' => $validated['jumlah_dibeli'],
            'tanggal_po' => $validated['tanggal_po'],
            'foto_path' => $fotoPath,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('admin_purchasing.pengadaan.index')
            ->with('success', 'Purchase Order #' . $pengadaan->id . ' berhasil dibuat.');
    }

    public function edit(Pengadaan $pengadaan)
    {
        // Block editing if it has already been received/processed by logistik
        if ($pengadaan->tanggal_kedatangan !== null) {
            return redirect()->route('admin_purchasing.pengadaan.index')
                ->with('error', 'PO yang sudah diproses penerimaannya oleh logistik tidak dapat diedit.');
        }

        $suppliers = Supplier::all();
        $produks = Produk::all();
        return view('admin_purchasing.pengadaan.edit', compact('pengadaan', 'suppliers', 'produks'));
    }

    public function update(Request $request, Pengadaan $pengadaan)
    {
        if ($pengadaan->tanggal_kedatangan !== null) {
            return redirect()->route('admin_purchasing.pengadaan.index')
                ->with('error', 'PO yang sudah diproses penerimaannya oleh logistik tidak dapat diedit.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:data_supplier,id',
            'produk_id' => 'required|exists:data_produk,id',
            'jumlah_dibeli' => 'required|integer|min:1',
            'tanggal_po' => 'required|date|before_or_equal:today',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'catatan' => 'nullable|string',
        ]);

        if ($request->hasFile('foto')) {
            // Delete old file if exists
            if ($pengadaan->foto_path) {
                $oldFile = str_replace('storage/pengadaan/', 'public/pengadaan/', $pengadaan->foto_path);
                Storage::delete($oldFile);
            }

            $file = $request->file('foto');
            $filename = time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/pengadaan', $filename);
            $pengadaan->foto_path = 'storage/pengadaan/' . $filename;
        }

        $pengadaan->update([
            'supplier_id' => $validated['supplier_id'],
            'produk_id' => $validated['produk_id'],
            'jumlah_dibeli' => $validated['jumlah_dibeli'],
            'tanggal_po' => $validated['tanggal_po'],
            'catatan' => $validated['catatan'] ?? null,
            'foto_path' => $pengadaan->foto_path,
        ]);

        return redirect()->route('admin_purchasing.pengadaan.index')
            ->with('success', 'Purchase Order #' . $pengadaan->id . ' berhasil diperbarui.');
    }

    public function destroy(Pengadaan $pengadaan)
    {
        if ($pengadaan->tanggal_kedatangan !== null) {
            return redirect()->route('admin_purchasing.pengadaan.index')
                ->with('error', 'PO yang sudah diproses penerimaannya oleh logistik tidak dapat dihapus.');
        }

        // Delete photo if exists
        if ($pengadaan->foto_path) {
            $fileToDelete = str_replace('storage/pengadaan/', 'public/pengadaan/', $pengadaan->foto_path);
            Storage::delete($fileToDelete);
        }

        $pengadaan->delete();

        return redirect()->route('admin_purchasing.pengadaan.index')
            ->with('success', 'Purchase Order berhasil dihapus.');
    }
}
