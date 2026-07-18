<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
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

        $tpsList = Tps::whereHas('desa', fn($q) => $q->where('kecamatan_id', $user->kecamatan_id))
            ->with(['desa', 'dataSuara'])
            ->orderBy('nama')
            ->get()
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
        ]);
    }
}
