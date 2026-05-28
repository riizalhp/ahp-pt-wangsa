<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Produk;
use App\Models\Kriteria;
use App\Models\Subkriteria;
use App\Models\HasilAhp;

class DashboardController extends Controller
{
    public function index()
    {
        $supplierCount = Supplier::count();
        $produkCount = Produk::count();
        $kriteriaCount = Kriteria::count();
        $subkriteriaCount = Subkriteria::count();
        
        $rankings = HasilAhp::with('supplier')
            ->orderBy('ranking', 'asc')
            ->take(5)
            ->get();

        return view('supervisor.dashboard', compact(
            'supplierCount',
            'produkCount',
            'kriteriaCount',
            'subkriteriaCount',
            'rankings'
        ));
    }
}
