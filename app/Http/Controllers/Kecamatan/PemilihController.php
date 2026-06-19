<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Pemilih;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PemilihController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user        = $request->user();
        $kecamatanId = $user->kecamatan_id;
        $kecamatanNama = $user->kecamatan->nama;

        $desaId = $request->query('desa_id');
        $search = $request->query('search');

        /** @var \Illuminate\Database\Eloquent\Builder<Pemilih> $query */
        $query = Pemilih::query()->with(['desa'])
            ->where('kecamatan_id', $kecamatanId);

        if ($desaId) {
            $query->where('desa_id', $desaId);
        }

        if ($search && is_numeric($search)) {
            $query->where('nik_hash', hash('sha256', $search));
        }

        if ($search && !is_numeric($search)) {
            // Text search (name search): must load and filter in memory
            $allPemilih = $query->orderBy('created_at', 'desc')->get();
            $allPemilih = $allPemilih->filter(function ($p) use ($search) {
                return str_contains(strtolower($p->nama), strtolower($search));
            });

            $countTotal = $allPemilih->count();
            $countLakiLaki = $allPemilih->where('jenis_kelamin', 'L')->count();
            $countPerempuan = $allPemilih->where('jenis_kelamin', 'P')->count();

            $perPage     = 20;
            $currentPage = (int) $request->query('page', 1);
            $sliced      = $allPemilih->slice(($currentPage - 1) * $perPage, $perPage);

            $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $sliced->map(fn ($p) => [
                    'id'            => $p->id,
                    'nik'           => $p->nik,
                    'nama'          => $p->nama,
                    'jenis_kelamin' => $p->jenis_kelamin,
                    'alamat'        => $p->alamat,
                    'rt'            => $p->rt,
                    'rw'            => $p->rw,
                    'desa'          => $p->desa->nama,
                    'created_at'    => $p->created_at->format('d/m/Y'),
                ])->values(),
                $countTotal,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            // No text search: Use database level pagination & counts
            $countTotal = $query->count();
            $countLakiLaki = (clone $query)->where('jenis_kelamin', 'L')->count();
            $countPerempuan = (clone $query)->where('jenis_kelamin', 'P')->count();

            $paginated = $query->orderBy('created_at', 'desc')->paginate(20)->through(fn ($p) => [
                'id'            => $p->id,
                'nik'           => $p->nik,
                'nama'          => $p->nama,
                'jenis_kelamin' => $p->jenis_kelamin,
                'alamat'        => $p->alamat,
                'rt'            => $p->rt,
                'rw'            => $p->rw,
                'desa'          => $p->desa->nama,
                'created_at'    => $p->created_at->format('d/m/Y'),
            ]);
        }

        /** @var \Illuminate\Database\Eloquent\Builder<Desa> $desaQuery */
        $desaQuery = Desa::query();

        return Inertia::render('kecamatan/Pemilih', [
            'pemilihs'   => $paginated,
            'desas'      => $desaQuery->where('kecamatan_id', $kecamatanId)->orderBy('nama', 'asc')->get(['id', 'nama']),
            'kecamatan'  => $kecamatanNama,
            'filters'    => [
                'desa_id' => $desaId,
                'search'  => $search,
            ],
            'summary'    => [
                'total' => $countTotal,
                'l'     => $countLakiLaki,
                'p'     => $countPerempuan,
            ],
        ]);
    }
}
