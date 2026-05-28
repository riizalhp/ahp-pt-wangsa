<?php

namespace App\Http\Controllers\Logistik;

use App\Http\Controllers\Controller;
use App\Models\Pengadaan;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingCount = Pengadaan::whereNull('tanggal_kedatangan')->count();
        $receivedCount = Pengadaan::whereNotNull('tanggal_kedatangan')->count();
        
        $pendingDeliveries = Pengadaan::with(['supplier', 'produk'])
            ->whereNull('tanggal_kedatangan')
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
