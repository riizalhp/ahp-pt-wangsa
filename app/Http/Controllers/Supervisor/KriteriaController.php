<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Kriteria;
use App\Models\Subkriteria;
use App\Models\PenilaianKriteria;
use Illuminate\Http\Request;

class KriteriaController extends Controller
{
    public function index()
    {
        $kriterias = Kriteria::withCount('subkriteria')->get();
        // Also list subcriteria grouped by criteria
        $subkriterias = Subkriteria::with('kriteria')->get()->groupBy('kriteria_id');
        
        return view('supervisor.kriteria.index', compact('kriterias', 'subkriterias'));
    }

    public function create()
    {
        return view('supervisor.kriteria.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:data_kriteria,kode',
            'nama' => 'required|string|max:120',
            'deskripsi' => 'nullable|string',
        ]);

        Kriteria::create($validated);

        return redirect()->route('supervisor.kriteria.index')
            ->with('success', 'Kriteria berhasil ditambahkan.');
    }

    public function edit(Kriteria $kriterium)
    {
        return view('supervisor.kriteria.edit', compact('kriterium'));
    }

    public function update(Request $request, Kriteria $kriterium)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:data_kriteria,kode,' . $kriterium->id,
            'nama' => 'required|string|max:120',
            'deskripsi' => 'nullable|string',
        ]);

        $kriterium->update($validated);

        return redirect()->route('supervisor.kriteria.index')
            ->with('success', 'Kriteria berhasil diperbarui.');
    }

    public function destroy(Kriteria $kriterium)
    {
        // Cascade-deletion guard (Req 3.5)
        $hasSub = Subkriteria::where('kriteria_id', $kriterium->id)->exists();
        $hasPenilaian = PenilaianKriteria::where('a_id', $kriterium->id)
            ->orWhere('b_id', $kriterium->id)
            ->exists();

        if ($hasSub || $hasPenilaian) {
            return redirect()->route('supervisor.kriteria.index')
                ->with('error', 'Kriteria "' . $kriterium->nama . '" tidak dapat dihapus karena memiliki subkriteria atau digunakan dalam penilaian.');
        }

        $kriterium->delete();

        return redirect()->route('supervisor.kriteria.index')
            ->with('success', 'Kriteria berhasil dihapus.');
    }
}
