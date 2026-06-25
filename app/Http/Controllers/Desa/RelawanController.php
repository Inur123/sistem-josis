<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use App\Models\AnggotaTim;
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

        // Get all volunteers in this village with their accompanied voters
        $relawans = AnggotaTim::query()
            ->where('desa_id', $user->desa_id)
            ->where('role', 'relawan')
            ->withCount('pemilihs')
            ->with(['pemilihs' => function ($query) {
                // Kirim hanya 10 pemilih pertama (halaman 1) saat load awal.
                $query->orderBy('created_at', 'desc')->limit(self::PER_PAGE);
            }])
            ->get()
            ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->map(fn ($r) => [
                'id' => $r->id,
                'nama' => $r->nama,
                'nik' => $r->nik,
                'no_hp' => $r->no_hp,
                'alamat' => $r->alamat,
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

        return Inertia::render('desa/relawan/Index', [
            'relawans' => $relawans,
            'desa' => $user->desa->nama,
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

        // Pastikan relawan ini memang dari desa user yang login
        abort_if(
            $relawan->role !== 'relawan' || $relawan->desa_id !== $user->desa_id,
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
