<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Pemilih;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class PemilihController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $kecamatanId = $user->kecamatan_id;

        // Cek jika request adalah AJAX fetch biasa dari Vue (bukan navigasi Inertia)
        if ($request->header('X-Requested-With') === 'XMLHttpRequest' && ! $request->header('X-Inertia')) {
            $result = $this->paginate(
                $request,
                scope: ['kecamatan_id' => $kecamatanId],
                extraColumns: ['desa']
            );

            return response()->json([
                'paginated' => $result['paginated'],
                'summary' => $result['summary'],
            ]);
        }

        $result = $this->paginate(
            $request,
            scope: ['kecamatan_id' => $kecamatanId],
            extraColumns: ['desa']
        );

        /** @var Builder<Desa> $desaQuery */
        $desaQuery = Desa::query();

        return Inertia::render('kecamatan/pemilih/Index', [
            'pemilihs' => $result['paginated'],
            'desas' => $desaQuery->where('kecamatan_id', $kecamatanId)->orderBy('nama', 'asc')->get(['id', 'nama']),
            'kecamatan' => $user->kecamatan->nama,
            'filters' => [
                'desa_id' => $request->query('desa_id'),
                'search' => $request->query('search'),
                'status' => $request->query('status'),
            ],
            'summary' => $result['summary'],
        ]);
    }

    private function paginate(Request $request, array $scope = [], array $extraColumns = []): array
    {
        $search = $request->query('search');
        $perPage = 20;
        /** @var Builder<Pemilih> $query */
        $query = Pemilih::query()->with(['kecamatan', 'desa', 'relawan'])->orderBy('created_at', 'desc');

        foreach ($scope as $column => $value) {
            if ($value) {
                $query->where($column, $value);
            }
        }

        // Extra optional filters passed via request
        if ($request->query('desa_id')) {
            $query->where('desa_id', $request->query('desa_id'));
        }
        if ($request->query('kecamatan_id')) {
            $query->where('kecamatan_id', $request->query('kecamatan_id'));
        }
        if ($request->query('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->query('jenis_kelamin'));
        }
        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($search && is_numeric($search)) {
            $query->where('nik_hash', hash('sha256', $search));
        }

        $formatRow = function ($p) use ($extraColumns) {
            $row = array_filter([
                'id' => $p->id,
                'nik' => $p->nik,
                'nama' => $p->nama,
                'jenis_kelamin' => $p->jenis_kelamin,
                'alamat' => $p->alamat,
                'rt' => $p->rt,
                'rw' => $p->rw,
                'kecamatan' => in_array('kecamatan', $extraColumns) ? $p->kecamatan?->nama : null,
                'desa' => in_array('desa', $extraColumns) ? $p->desa?->nama : null,
                'relawan' => $p->relawan?->nama,
                'created_at' => $p->created_at?->format('d/m/Y'),
            ], fn ($v) => $v !== null);

            $row['status'] = $p->status;
            $row['alasan_ditolak'] = $p->alasan_ditolak;

            return $row;
        };

        if ($search && ! is_numeric($search)) {
            // In-memory name search (required because nama is encrypted)
            $all = $query->get()->filter(
                fn ($p) => str_contains(strtolower($p->nama), strtolower($search))
            );

            $countTotal = $all->count();
            $countL = $all->where('jenis_kelamin', 'L')->count();
            $countP = $all->where('jenis_kelamin', 'P')->count();
            $countBelum = $all->where('status', 'belum_verifikasi')->count();
            $countTerverifikasi = $all->where('status', 'terverifikasi')->count();
            $countDitolak = $all->where('status', 'ditolak')->count();
            $currentPage = (int) $request->query('page', 1);

            $paginated = new LengthAwarePaginator(
                $all->slice(($currentPage - 1) * $perPage, $perPage)->map($formatRow)->values(),
                $countTotal,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $countTotal = $query->count();
            $countL = (clone $query)->where('jenis_kelamin', 'L')->count();
            $countP = (clone $query)->where('jenis_kelamin', 'P')->count();
            $countBelum = (clone $query)->where('status', 'belum_verifikasi')->count();
            $countTerverifikasi = (clone $query)->where('status', 'terverifikasi')->count();
            $countDitolak = (clone $query)->where('status', 'ditolak')->count();
            $paginated = $query->paginate($perPage)->through($formatRow);
        }

        return [
            'paginated' => $paginated,
            'summary' => [
                'total' => $countTotal,
                'l' => $countL,
                'p' => $countP,
                'belum_verifikasi' => $countBelum,
                'terverifikasi' => $countTerverifikasi,
                'ditolak' => $countDitolak,
            ],
        ];
    }

    /**
     * Detail pemilih untuk Kecamatan.
     */
    public function show(Request $request, Pemilih $pemilih): Response
    {
        /** @var User $user */
        $user = $request->user();
        abort_if($pemilih->kecamatan_id !== $user->kecamatan_id, 403);

        return Inertia::render('kecamatan/pemilih/Show', [
            'desa' => $pemilih->desa->nama,
            'pemilih' => [
                'id' => $pemilih->id,
                'nik' => $pemilih->nik,
                'nama' => $pemilih->nama,
                'jenis_kelamin' => $pemilih->jenis_kelamin,
                'alamat' => $pemilih->alamat,
                'rt' => $pemilih->rt,
                'rw' => $pemilih->rw,
                'relawan' => $pemilih->relawan?->nama,
                'created_at' => $pemilih->created_at?->format('d/m/Y'),
                'foto_ktp' => $pemilih->foto_ktp ? route('pemilih.ktp', $pemilih->id) : null,
                'status' => $pemilih->status,
                'alasan_ditolak' => $pemilih->alasan_ditolak,
            ],
        ]);
    }
}
