<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Tps;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TpsController extends Controller
{
    public function index(Request $request): Response
    {
        $kecamatanId = $request->query('kecamatan_id');
        $desaId = $request->query('desa_id');

        // Always load filter dropdowns (lightweight)
        $kecamatans = Kecamatan::orderBy('nama')->get(['id', 'nama']);

        $desaQuery = Desa::query();
        if ($kecamatanId) {
            $desaQuery->where('kecamatan_id', $kecamatanId);
        }
        $desas = $desaQuery->orderBy('nama')->get(['id', 'nama', 'kecamatan_id']);

        $tpsList = collect();

        // Only load TPS data if a kecamatan filter is selected (filter-first pattern)
        if ($kecamatanId) {
            $tpsList = Tps::whereHas('desa', fn($q) => $q->where('kecamatan_id', $kecamatanId))
                ->with(['desa', 'dataSuara'])
                ->when($desaId, fn($q) => $q->where('desa_id', $desaId))
                ->orderBy('nama')
                ->get()
                ->map(fn(Tps $tps): array => [
                    'id'          => $tps->id,
                    'nama'        => $tps->nama,
                    'desa'        => $tps->desa?->nama ?? '-',
                    'kecamatan'   => $tps->desa?->kecamatan?->nama ?? '-',
                    'total_suara' => $tps->dataSuara?->total_suara ?? null,
                    'has_c_hasil' => (bool) $tps->dataSuara?->c_hasil_path,
                    'c_hasil_url' => $tps->dataSuara?->c_hasil_path
                        ? route('tps.c-hasil', $tps->id)
                        : null,
                    'sudah_diisi' => $tps->dataSuara !== null,
                ]);
        }

        return Inertia::render('admin/tps/Index', [
            'tpsList'    => $tpsList,
            'totalTps'   => $tpsList->count(),
            'kecamatans' => $kecamatans,
            'desas'      => $desas,
            'filters'    => [
                'kecamatan_id' => $kecamatanId,
                'desa_id'      => $desaId,
            ],
        ]);
    }
}
