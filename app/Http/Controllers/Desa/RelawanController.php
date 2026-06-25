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
            ->map(function ($r) {
                $counts = \Illuminate\Support\Facades\DB::table('pemilihs')
                    ->where('relawan_id', $r->id)
                    ->selectRaw("
                        COUNT(*) as total_count,
                        SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as l_count,
                        SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as p_count,
                        SUM(CASE WHEN status = 'belum_verifikasi' THEN 1 ELSE 0 END) as belum_verifikasi_count,
                        SUM(CASE WHEN status = 'terverifikasi' THEN 1 ELSE 0 END) as terverifikasi_count,
                        SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak_count
                    ")->first();

                return [
                    'id' => $r->id,
                    'nama' => $r->nama,
                    'nik' => $r->nik,
                    'no_hp' => $r->no_hp,
                    'alamat' => $r->alamat,
                    'pemilihs_count' => $counts->total_count,
                    'summary' => [
                        'total' => $counts->total_count,
                        'l' => $counts->l_count,
                        'p' => $counts->p_count,
                        'belum_verifikasi' => $counts->belum_verifikasi_count,
                        'terverifikasi' => $counts->terverifikasi_count,
                        'ditolak' => $counts->ditolak_count,
                    ],
                    'pemilihs' => $r->pemilihs->map(fn ($p) => [
                        'id' => $p->id,
                        'nik' => $p->nik,
                        'nama' => $p->nama,
                        'jenis_kelamin' => $p->jenis_kelamin,
                        'alamat' => $p->alamat,
                        'rt' => $p->rt,
                        'rw' => $p->rw,
                        'created_at' => $p->created_at?->format('d/m/Y'),
                        'status' => $p->status,
                        'alasan_ditolak' => $p->alasan_ditolak,
                    ])->values(),
                ];
            });

        return Inertia::render('desa/relawan/Index', [
            'relawans' => $relawans,
            'desa' => $user->desa->nama,
        ]);
    }

    public function show(Request $request, AnggotaTim $relawan): Response
    {
        /** @var User $user */
        $user = $request->user();

        // Pastikan relawan ini memang dari desa user yang login
        abort_if(
            $relawan->role !== 'relawan' || $relawan->desa_id !== $user->desa_id,
            403
        );

        $counts = \Illuminate\Support\Facades\DB::table('pemilihs')
            ->where('relawan_id', $relawan->id)
            ->selectRaw("
                COUNT(*) as total_count,
                SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as l_count,
                SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as p_count,
                SUM(CASE WHEN status = 'belum_verifikasi' THEN 1 ELSE 0 END) as belum_verifikasi_count,
                SUM(CASE WHEN status = 'terverifikasi' THEN 1 ELSE 0 END) as terverifikasi_count,
                SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak_count
            ")->first();

        // Get first page of voters
        $pemilihs = Pemilih::query()
            ->where('relawan_id', $relawan->id)
            ->orderBy('created_at', 'desc')
            ->limit(self::PER_PAGE)
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
                'status' => $p->status,
                'alasan_ditolak' => $p->alasan_ditolak,
            ]);

        $relawanData = [
            'id' => $relawan->id,
            'nama' => $relawan->nama,
            'nik' => $relawan->nik,
            'no_hp' => $relawan->no_hp,
            'alamat' => $relawan->alamat,
            'desa' => $user->desa->nama,
            'pemilihs_count' => $counts->total_count,
            'summary' => [
                'total' => $counts->total_count,
                'l' => $counts->l_count,
                'p' => $counts->p_count,
                'belum_verifikasi' => $counts->belum_verifikasi_count,
                'terverifikasi' => $counts->terverifikasi_count,
                'ditolak' => $counts->ditolak_count,
            ],
            'pemilihs' => $pemilihs,
        ];

        return Inertia::render('desa/relawan/Show', [
            'relawan' => $relawanData,
        ]);
    }

    public function showPemilih(Request $request, AnggotaTim $relawan, Pemilih $pemilih): Response
    {
        /** @var User $user */
        $user = $request->user();
        abort_if(
            $relawan->role !== 'relawan' || 
            $relawan->desa_id !== $user->desa_id || 
            $pemilih->relawan_id !== $relawan->id, 
            403
        );

        return Inertia::render('desa/pemilih/Show', [
            'desa' => $user->desa->nama,
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
                'status' => $p->status,
                'alasan_ditolak' => $p->alasan_ditolak,
            ]);

        return response()->json([
            'data' => $pemilihs->values(),
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }
}
