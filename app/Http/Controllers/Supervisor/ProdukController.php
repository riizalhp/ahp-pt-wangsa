<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisor\ProdukRequest;
use App\Models\PengadaanDetail;
use App\Models\Produk;
use App\Models\Supplier;
use App\Support\NameSearch;
use Illuminate\Http\Request;

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
        Produk::create($request->validated());

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
        $produk->update($request->validated());

        return redirect()->route('supervisor.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Delete the specified product, guarded against use in procurement details.
     * Requirements: 2.8
     */
    public function destroy(Produk $produk)
    {
        if (PengadaanDetail::where('produk_id', $produk->id)->exists()) {
            return redirect()->route('supervisor.produk.index')
                ->with('error', 'Produk tidak dapat dihapus karena masih digunakan dalam data pengadaan.');
        }

        $produk->delete();

        return redirect()->route('supervisor.produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
