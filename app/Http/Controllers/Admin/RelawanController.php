<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnggotaTim;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Pemilih;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RelawanController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request): Response
    {
        $kecamatanId = $request->query('kecamatan_id');
        $desaId = $request->query('desa_id');

        // Fetch all kecamatan and desas for dropdown filters
        $kecamatans = Kecamatan::query()->orderBy('nama', 'asc')->get(['id', 'nama']);

        $desaQuery = Desa::query();
        if ($kecamatanId) {
            $desaQuery->where('kecamatan_id', $kecamatanId);
        }
        $desas = $desaQuery->orderBy('nama', 'asc')->get(['id', 'nama', 'kecamatan_id']);

        $relawans = collect();

        // Query relawans only if kecamatan is selected
        if ($kecamatanId) {
            $query = AnggotaTim::query()
                ->where('role', 'relawan')
                ->withCount('pemilihs')
                ->with(['kecamatan', 'desa', 'pemilihs' => function ($q) {
                    // Kirim hanya 10 pemilih pertama (halaman 1) saat load awal.
                    // Halaman berikutnya di-fetch via AJAX on-demand.
                    $q->orderBy('created_at', 'desc')->limit(self::PER_PAGE);
                }]);

            $query->where('kecamatan_id', $kecamatanId);
            if ($desaId) {
                $query->where('desa_id', $desaId);
            }

            $relawans = $query->get()
                ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'nama' => $r->nama,
                    'nik' => $r->nik,
                    'no_hp' => $r->no_hp,
                    'alamat' => $r->alamat,
                    'kecamatan' => $r->kecamatan?->nama ?? '-',
                    'desa' => $r->desa?->nama ?? '-',
                    'pemilihs_count' => $r->pemilihs_count,
                    'pemilihs' => $r->pemilihs->map(fn ($p) => [
                        'id' => $p->id,
                        'nik' => $p->nik,
                        'nama' => $p->nama,
                        'jenis_kelamin' => $p->jenis_kelamin,
                        'alamat' => $p->alamat,
                        'rt' => $p->rt,
                        'rw' => $p->rw,
                        'created_at' => $p->created_at?->format('d/m/Y'),
                    ])->values(),
                ]);
        }

        return Inertia::render('admin/relawan/Index', [
            'relawans' => $relawans,
            'kecamatans' => $kecamatans,
            'desas' => $desas,
            'filters' => [
                'kecamatan_id' => $kecamatanId,
                'desa_id' => $desaId,
            ],
        ]);
    }

    /**
     * JSON endpoint: kembalikan halaman ke-N dari pemilih milik satu relawan.
     * Dipanggil via fetch() di frontend saat user klik Next/Prev.
     */
    public function pemilihs(Request $request, AnggotaTim $relawan): JsonResponse
    {
        // Pastikan relawan ini memang berrole 'relawan'
        abort_if($relawan->role !== 'relawan', 403);

        $page = max(1, (int) $request->query('page', 1));
        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $pemilihs = Pemilih::query()
            ->where('relawan_id', $relawan->id)
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nik' => $p->nik,
                'nama' => $p->nama,
                'jenis_kelamin' => $p->jenis_kelamin,
                'alamat' => $p->alamat,
                'rt' => $p->rt,
                'rw' => $p->rw,
                'created_at' => $p->created_at?->format('d/m/Y'),
            ]);

        return response()->json([
            'data' => $pemilihs->values(),
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }
}
