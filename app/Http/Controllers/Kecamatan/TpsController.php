<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TpsController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $desaId = $request->query('desa_id');

        // Fetch desas for filter dropdown
        $desas = Desa::query()
            ->where('kecamatan_id', $user->kecamatan_id)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nama']);

        $query = Tps::query()
            ->whereHas('desa', fn($q) => $q->where('kecamatan_id', $user->kecamatan_id))
            ->with(['desa', 'dataSuara'])
            ->orderBy('nama');

        if ($desaId) {
            $query->where('desa_id', $desaId);
        }

        $tpsList = $query->get()
            ->map(fn(Tps $tps): array => [
                'id'          => $tps->id,
                'nama'        => $tps->nama,
                'desa'        => $tps->desa?->nama ?? '-',
                'total_suara' => $tps->dataSuara?->total_suara ?? null,
                'has_c_hasil' => (bool) $tps->dataSuara?->c_hasil_path,
                'c_hasil_url' => $tps->dataSuara?->c_hasil_path
                    ? route('tps.c-hasil', $tps->id)
                    : null,
                'sudah_diisi' => $tps->dataSuara !== null,
                'created_at'  => $tps->created_at?->format('d/m/Y'),
            ]);

        return Inertia::render('kecamatan/tps/Index', [
            'tpsList'    => $tpsList,
            'totalTps'   => $tpsList->count(),
            'kecamatan'  => $user->kecamatan?->nama,
            'desas'      => $desas,
            'filters'    => [
                'desa_id' => $desaId,
            ],
        ]);
    }
}
