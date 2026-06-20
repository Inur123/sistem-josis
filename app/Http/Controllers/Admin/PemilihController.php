<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Services\PemilihService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PemilihController extends Controller
{
    public function __construct(private PemilihService $pemilihService) {}

    public function index(Request $request): Response
    {
        $result = $this->pemilihService->paginate(
            $request,
            scope: [],
            extraColumns: ['kecamatan', 'desa']
        );

        /** @var \Illuminate\Database\Eloquent\Builder<Kecamatan> $kecamatanQuery */
        $kecamatanQuery = Kecamatan::query();

        /** @var \Illuminate\Database\Eloquent\Builder<Desa> $desaQuery */
        $desaQuery = Desa::query();

        return Inertia::render('admin/Pemilih', [
            'pemilihs'   => $result['paginated'],
            'kecamatans' => $kecamatanQuery->orderBy('nama', 'asc')->get(['id', 'nama']),
            'desas'      => $desaQuery->orderBy('nama', 'asc')->get(['id', 'nama', 'kecamatan_id']),
            'filters'    => [
                'kecamatan_id' => $request->query('kecamatan_id'),
                'desa_id'      => $request->query('desa_id'),
                'search'       => $request->query('search'),
            ],
            'summary'    => $result['summary'],
        ]);
    }
}
