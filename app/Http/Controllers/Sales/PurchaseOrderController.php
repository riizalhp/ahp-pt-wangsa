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
        $produks = Produk::with('supplier')->orderBy('nama')->get();
        $satuanList = ['Rim', 'Pcs', 'Ltr', 'Lbr', 'Kg', 'Pack', 'Roll'];

        return view('sales.purchase_order.create', compact('suppliers', 'produks', 'satuanList'));
    }

    public function store(PurchaseOrderRequest $request)
    {
        DB::transaction(function () use ($request) {
            $header = PengadaanHeader::create($request->only([
                'supplier_id',
                'no_po',
                'tanggal_po',
                'tanggal_kedatangan_target',
                'catatan',
            ]));

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
}
