<?php

namespace App\Http\Controllers\Logistik;

use App\Http\Controllers\Controller;
use App\Models\PengadaanHeader;
use App\Models\PengadaanDetail;

class DashboardController extends Controller
{
    public function index()
    {
        // Pending = PO headers that have at least one detail where jumlah_diterima_baik IS NULL
        $pendingCount = PengadaanHeader::whereHas('detail', function ($query) {
            $query->whereNull('jumlah_diterima_baik');
        })->count();
        
        // Received = PO headers where all details have been received (none are NULL)
        $receivedCount = PengadaanHeader::whereDoesntHave('detail', function ($query) {
            $query->whereNull('jumlah_diterima_baik');
        })->where(function ($query) {
            $query->whereHas('detail');
        })->count();
        
        // Pending deliveries: PO headers with at least one unreceived item, ordered by PO date
        $pendingDeliveries = PengadaanHeader::with(['supplier', 'detail.produk'])
            ->whereHas('detail', function ($query) {
                $query->whereNull('jumlah_diterima_baik');
            })
            ->orderBy('tanggal_po', 'asc')
            ->take(5)
            ->get();

        return view('logistik.dashboard', compact(
            'pendingCount',
            'receivedCount',
            'pendingDeliveries'
        ));
    }
}
