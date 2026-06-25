<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\AnggotaTim;
use App\Models\Desa;
use App\Models\Pemilih;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RelawanController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $kecamatanId = $user->kecamatan_id;
        $desaId = $request->query('desa_id');

        // Fetch all desas in this kecamatan for the filter dropdown
        $desas = Desa::query()
            ->where('kecamatan_id', $kecamatanId)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nama']);

        // Query relawans in this kecamatan
        $query = AnggotaTim::query()
            ->where('kecamatan_id', $kecamatanId)
            ->where('role', 'relawan')
            ->withCount('pemilihs')
            ->with(['desa', 'pemilihs' => function ($q) {
                // Kirim hanya 10 pemilih pertama (halaman 1) saat load awal.
                $q->orderBy('created_at', 'desc')->limit(self::PER_PAGE);
            }]);

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

        return Inertia::render('kecamatan/relawan/Index', [
            'relawans' => $relawans,
            'desas' => $desas,
            'kecamatan' => $user->kecamatan->nama,
            'filters' => [
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
        /** @var User $user */
        $user = $request->user();

        // Pastikan relawan ini memang dari kecamatan user yang login
        abort_if(
            $relawan->role !== 'relawan' || $relawan->kecamatan_id !== $user->kecamatan_id,
            403
        );

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
