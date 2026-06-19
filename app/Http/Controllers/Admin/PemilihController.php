<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Pemilih;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PemilihController extends Controller
{
    public function index(Request $request): Response
    {
        $kecamatanId = $request->query('kecamatan_id');
        $desaId = $request->query('desa_id');
        $search = $request->query('search');

        $query = Pemilih::with(['kecamatan', 'desa'])
            ->orderBy('created_at', 'desc');

        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        if ($desaId) {
            $query->where('desa_id', $desaId);
        }

        if ($search && is_numeric($search)) {
            $query->where('nik_hash', hash('sha256', $search));
        }

        $allPemilih = $query->get();

        if ($search && !is_numeric($search)) {
            $allPemilih = $allPemilih->filter(function ($p) use ($search) {
                return str_contains(strtolower($p->nama), strtolower($search));
            });
        }

        $perPage = 20;
        $currentPage = (int) $request->query('page', 1);
        $total = $allPemilih->count();
        $sliced = $allPemilih->slice(($currentPage - 1) * $perPage, $perPage);

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced->map(fn ($p) => [
                'id'            => $p->id,
                'nik'           => $p->nik,
                'nama'          => $p->nama,
                'jenis_kelamin' => $p->jenis_kelamin,
                'alamat'        => $p->alamat,
                'rt'            => $p->rt,
                'rw'            => $p->rw,
                'kecamatan'     => $p->kecamatan->nama,
                'desa'          => $p->desa->nama,
                'created_at'    => $p->created_at->format('d/m/Y'),
            ])->values(),
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('admin/Pemilih', [
            'pemilihs' => $paginated,
            'kecamatans' => Kecamatan::orderBy('nama')->get(['id', 'nama']),
            'desas' => Desa::orderBy('nama')->get(['id', 'nama', 'kecamatan_id']),
            'filters' => [
                'kecamatan_id' => $kecamatanId,
                'desa_id' => $desaId,
                'search' => $search,
            ],
        ]);
    }
}
