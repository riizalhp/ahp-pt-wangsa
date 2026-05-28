<?php

namespace App\Http\Controllers\Logistik;

use App\Http\Controllers\Controller;
use App\Models\Pengadaan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AktualPengadaanController extends Controller
{
    public function index()
    {
        $pendingPos = Pengadaan::with(['supplier', 'produk'])
            ->whereNull('tanggal_kedatangan')
            ->orderBy('tanggal_po', 'asc')
            ->get();

        $completedPos = Pengadaan::with(['supplier', 'produk'])
            ->whereNotNull('tanggal_kedatangan')
            ->orderBy('tanggal_kedatangan', 'desc')
            ->get();

        return view('logistik.aktual.index', compact('pendingPos', 'completedPos'));
    }

    public function edit($id)
    {
        $pengadaan = Pengadaan::with(['supplier', 'produk'])->findOrFail($id);
        return view('logistik.aktual.edit', compact('pengadaan'));
    }

    public function update(Request $request, $id)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        $validated = $request->validate([
            'tanggal_kedatangan' => 'required|date|after_or_equal:tanggal_po',
            'jumlah_diterima' => 'required|integer|min:0|lte:jumlah_dibeli',
            'jumlah_cacat' => 'required|integer|min:0|lte:jumlah_diterima',
        ]);

        $jumlahDibeli = $pengadaan->jumlah_dibeli;
        $jumlahDiterima = intval($validated['jumlah_diterima']);
        $jumlahCacat = intval($validated['jumlah_cacat']);

        // Calculate persen_kualitas (Req 9.2)
        $persenKualitas = (($jumlahDiterima - $jumlahCacat) / $jumlahDibeli) * 100.0;

        // Calculate hari_keterlambatan (Req 9.3)
        $poDate = Carbon::parse($pengadaan->tanggal_po);
        $arrivalDate = Carbon::parse($validated['tanggal_kedatangan']);
        $hariKeterlambatan = intval($poDate->diffInDays($arrivalDate, false)); // positive if arrival > po

        $pengadaan->update([
            'tanggal_kedatangan' => $validated['tanggal_kedatangan'],
            'jumlah_diterima' => $jumlahDiterima,
            'jumlah_cacat' => $jumlahCacat,
            'persen_kualitas' => $persenKualitas,
            'hari_keterlambatan' => $hariKeterlambatan,
        ]);

        return redirect()->route('logistik.aktual.index')
            ->with('success', 'Data penerimaan PO #' . $pengadaan->id . ' berhasil diperbarui dan rekap supplier telah diupdate.');
    }
}
