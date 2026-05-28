<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Pengadaan;

class DashboardController extends Controller
{
    public function index()
    {
        $poCount = Pengadaan::count();
        $pendingCount = Pengadaan::whereNull('tanggal_kedatangan')->count();
        $receivedCount = Pengadaan::whereNotNull('tanggal_kedatangan')->count();
        
        $latestPos = Pengadaan::with(['supplier', 'produk'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('sales.dashboard', compact(
            'poCount',
            'pendingCount',
            'receivedCount',
            'latestPos'
        ));
    }
}
