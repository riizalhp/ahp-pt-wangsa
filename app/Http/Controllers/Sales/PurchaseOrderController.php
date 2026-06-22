<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\PurchaseOrderRequest;
use App\Models\PengadaanHeader;
use App\Models\PengadaanDetail;
use App\Models\Supplier;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $headers = PengadaanHeader::with(['supplier', 'detail.produk'])
            ->orderBy('id', 'desc')
            ->get();

        return view('sales.purchase_order.index', compact('headers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('nama')->get();
        $produks = Produk::with('supplier')
            ->orderBy('nama')
            ->get()
            ->map(function($produk) {
                return [
                    'id' => $produk->id,
                    'nama' => $produk->nama,
                    'jenis_produk' => $produk->jenis_produk,
                    'supplier_id' => $produk->supplier_id,
                ];
            });
        $satuanList = ['Rim', 'Pcs', 'Ltr', 'Lbr', 'Kg', 'Pack', 'Roll'];

        return view('sales.purchase_order.create', compact('suppliers', 'produks', 'satuanList'));
    }

    public function store(PurchaseOrderRequest $request)
    {
        DB::transaction(function () use ($request) {
            $headerData = $request->only([
                'supplier_id',
                'no_po',
                'tanggal_po',
                'tanggal_kedatangan_target',
                'catatan',
            ]);

            // Handle photo upload
            if ($request->hasFile('foto')) {
                $headerData['foto'] = $request->file('foto')->store('purchase_orders', 'public');
            }

            $header = PengadaanHeader::create($headerData);

            foreach ($request->items as $item) {
                PengadaanDetail::create([
                    'pengadaan_id'  => $header->id,
                    'produk_id'     => $item['produk_id'],
                    'jumlah_dipesan' => $item['jumlah_dipesan'],
                    'satuan'        => $item['satuan'],
                ]);
            }
        });

        return redirect()->route('sales.purchase_order.index')
            ->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function show(PengadaanHeader $purchase_order)
    {
        $purchase_order->load('detail.produk');

        return view('sales.purchase_order.show', compact('purchase_order'));
    }

    public function edit(PengadaanHeader $purchase_order)
    {
        // Only allow editing if no items have been received yet
        $hasReceivedItems = $purchase_order->detail()->whereNotNull('jumlah_diterima_baik')->exists();
        
        if ($hasReceivedItems) {
            return redirect()->route('sales.purchase_order.index')
                ->with('error', 'Purchase Order yang sudah diterima tidak dapat diedit.');
        }

        $suppliers = Supplier::orderBy('nama')->get();
        $produks = Produk::with('supplier')->orderBy('nama')->get();
        $satuanList = ['Rim', 'Pcs', 'Ltr', 'Lbr', 'Kg', 'Pack', 'Roll'];

        $purchase_order->load('detail.produk');

        return view('sales.purchase_order.edit', compact('purchase_order', 'suppliers', 'produks', 'satuanList'));
    }

    public function update(PurchaseOrderRequest $request, PengadaanHeader $purchase_order)
    {
        // Only allow updating if no items have been received yet
        $hasReceivedItems = $purchase_order->detail()->whereNotNull('jumlah_diterima_baik')->exists();
        
        if ($hasReceivedItems) {
            return redirect()->route('sales.purchase_order.index')
                ->with('error', 'Purchase Order yang sudah diterima tidak dapat diedit.');
        }

        DB::transaction(function () use ($request, $purchase_order) {
            $headerData = $request->only([
                'supplier_id',
                'tanggal_po',
                'tanggal_kedatangan_target',
                'catatan',
            ]);

            // Handle photo upload
            if ($request->hasFile('foto')) {
                // Delete old photo if exists
                if ($purchase_order->foto && \Storage::disk('public')->exists($purchase_order->foto)) {
                    \Storage::disk('public')->delete($purchase_order->foto);
                }
                $headerData['foto'] = $request->file('foto')->store('purchase_orders', 'public');
            }

            $purchase_order->update($headerData);

            // Delete existing details and recreate
            $purchase_order->detail()->delete();

            foreach ($request->items as $item) {
                PengadaanDetail::create([
                    'pengadaan_id'  => $purchase_order->id,
                    'produk_id'     => $item['produk_id'],
                    'jumlah_dipesan' => $item['jumlah_dipesan'],
                    'satuan'        => $item['satuan'],
                ]);
            }
        });

        return redirect()->route('sales.purchase_order.index')
            ->with('success', 'Purchase Order berhasil diperbarui.');
    }

    public function destroy(PengadaanHeader $purchase_order)
    {
        // Only allow deleting if no items have been received yet
        $hasReceivedItems = $purchase_order->detail()->whereNotNull('jumlah_diterima_baik')->exists();
        
        if ($hasReceivedItems) {
            return redirect()->route('sales.purchase_order.index')
                ->with('error', 'Purchase Order yang sudah diterima tidak dapat dihapus.');
        }

        DB::transaction(function () use ($purchase_order) {
            // Delete photo if exists
            if ($purchase_order->foto && \Storage::disk('public')->exists($purchase_order->foto)) {
                \Storage::disk('public')->delete($purchase_order->foto);
            }

            // Delete details
            $purchase_order->detail()->delete();

            // Delete header
            $purchase_order->delete();
        });

        return redirect()->route('sales.purchase_order.index')
            ->with('success', 'Purchase Order berhasil dihapus.');
    }
}
