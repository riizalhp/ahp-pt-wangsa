<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\PengadaanHeader;
use App\Models\PengadaanDetail;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Purchase Order (Header count)
        $poCount = PengadaanHeader::count();
        
        // Pending = headers that have at least one detail where jumlah_diterima_baik IS NULL
        $pendingCount = PengadaanHeader::whereHas('detail', function ($query) {
            $query->whereNull('jumlah_diterima_baik');
        })->count();
        
        // Received = headers where all details have been received (none are NULL)
        $receivedCount = PengadaanHeader::whereDoesntHave('detail', function ($query) {
            $query->whereNull('jumlah_diterima_baik');
        })->where(function ($query) {
            $query->whereHas('detail');
        })->count();
        
        // Latest 5 Purchase Orders with details
        $latestPos = PengadaanHeader::with(['supplier', 'detail.produk'])
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
