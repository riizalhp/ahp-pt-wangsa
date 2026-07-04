<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Subkriteria;
use App\Models\Kriteria;
use App\Models\PenilaianSubkriteria;
use App\Models\PenilaianSupplier;
use Illuminate\Http\Request;

class SubkriteriaController extends Controller
{
    public function create(Request $request)
    {
        $kriterias = Kriteria::all();
        $selectedKriteriaId = $request->query('kriteria_id');
        return view('supervisor.subkriteria.create', compact('kriterias', 'selectedKriteriaId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kriteria_id' => 'required|exists:data_kriteria,id',
            'kode' => 'required|string',
            'nama' => 'required|string|max:120',
            'deskripsi' => 'nullable|string',
        ]);

        // Check uniqueness for kriteria_id + kode
        $exists = Subkriteria::where('kriteria_id', $validated['kriteria_id'])
            ->where('kode', $validated['kode'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['kode' => 'Kode subkriteria sudah digunakan untuk kriteria ini.'])->withInput();
        }

        Subkriteria::create($validated);

        return redirect()->route('supervisor.kriteria.index')
            ->with('success', 'Subkriteria berhasil ditambahkan.');
    }

    public function edit(Subkriteria $subkriterium)
    {
        $kriterias = Kriteria::all();
        return view('supervisor.subkriteria.edit', compact('subkriterium', 'kriterias'));
    }

    public function update(Request $request, Subkriteria $subkriterium)
    {
        $validated = $request->validate([
            'kriteria_id' => 'required|exists:data_kriteria,id',
            'kode' => 'required|string',
            'nama' => 'required|string|max:120',
            'deskripsi' => 'nullable|string',
        ]);

        // Check uniqueness for kriteria_id + kode excluding this subkriteria
        $exists = Subkriteria::where('kriteria_id', $validated['kriteria_id'])
            ->where('kode', $validated['kode'])
            ->where('id', '!=', $subkriterium->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['kode' => 'Kode subkriteria sudah digunakan untuk kriteria ini.'])->withInput();
        }

        $subkriterium->update($validated);

        return redirect()->route('supervisor.kriteria.index')
            ->with('success', 'Subkriteria berhasil diperbarui.');
    }

    public function destroy(Subkriteria $subkriterium)
    {
        // Check if used in penilaian subkriteria first
        $hasPenilaianSub = PenilaianSubkriteria::where('a_id', $subkriterium->id)
            ->orWhere('b_id', $subkriterium->id)
            ->exists();

        // Check if used in penilaian supplier
        $hasPenilaianSup = PenilaianSupplier::where('subkriteria_id', $subkriterium->id)->exists();

        if ($hasPenilaianSub || $hasPenilaianSup) {
            return redirect()->route('supervisor.kriteria.index')
                ->with('error', 'Subkriteria "' . $subkriterium->nama . '" tidak dapat dihapus karena masih dalam penilaian. Gunakan tombol "Reset Penilaian" di halaman Laporan Penilaian untuk reset semua data penilaian terlebih dahulu.');
        }

        $subkriterium->delete();

        return redirect()->route('supervisor.kriteria.index')
            ->with('success', 'Subkriteria berhasil dihapus.');
    }
}
