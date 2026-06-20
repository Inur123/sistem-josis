<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Services\PemilihService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PemilihController extends Controller
{
    public function __construct(private PemilihService $pemilihService) {}

    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user        = $request->user();
        $kecamatanId = $user->kecamatan_id;

        $result = $this->pemilihService->paginate(
            $request,
            scope: ['kecamatan_id' => $kecamatanId],
            extraColumns: ['desa']
        );

        /** @var \Illuminate\Database\Eloquent\Builder<Desa> $desaQuery */
        $desaQuery = Desa::query();

        return Inertia::render('kecamatan/Pemilih', [
            'pemilihs'  => $result['paginated'],
            'desas'     => $desaQuery->where('kecamatan_id', $kecamatanId)->orderBy('nama', 'asc')->get(['id', 'nama']),
            'kecamatan' => $user->kecamatan->nama,
            'filters'   => [
                'desa_id' => $request->query('desa_id'),
                'search'  => $request->query('search'),
            ],
            'summary'   => $result['summary'],
        ]);
    }
}
