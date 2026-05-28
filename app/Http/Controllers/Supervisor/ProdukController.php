<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Pengadaan;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produk::all();
        return view('supervisor.produk.index', compact('produks'));
    }

    public function create()
    {
        return view('supervisor.produk.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:data_produk,kode',
            'nama' => 'required|string|max:120',
            'satuan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
        ]);

        Produk::create($validated);

        return redirect()->route('supervisor.produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        return view('supervisor.produk.edit', compact('produk'));
    }

    public function update(Request $request, Produk $produk)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:data_produk,kode,' . $produk->id,
            'nama' => 'required|string|max:120',
            'satuan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
        ]);

        $produk->update($validated);

        return redirect()->route('supervisor.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        // Cascade-deletion guard (Req 2.6)
        $hasPo = Pengadaan::where('produk_id', $produk->id)->exists();

        if ($hasPo) {
            return redirect()->route('supervisor.produk.index')
                ->with('error', 'Produk "' . $produk->nama . '" tidak dapat dihapus karena masih digunakan dalam data pengadaan.');
        }

        $produk->delete();

        return redirect()->route('supervisor.produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
