<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use App\Models\AnggotaTim;
use App\Models\Pemilih;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PemilihController extends Controller
{
    /**
     * Daftar pemilih di desa user yang login.
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // Cek jika request adalah AJAX fetch biasa dari Vue (bukan navigasi Inertia)
        if ($request->header('X-Requested-With') === 'XMLHttpRequest' && !$request->header('X-Inertia')) {
            $result = $this->paginate(
                $request,
                scope: ['desa_id' => $user->desa_id],
            );
            return response()->json([
                'paginated' => $result['paginated'],
                'summary' => $result['summary'],
            ]);
        }

        $result = $this->paginate(
            $request,
            scope: ['desa_id' => $user->desa_id],
        );

        return Inertia::render('desa/Pemilih', [
            'pemilihs' => $result['paginated'],
            'desa' => $user->desa->nama,
            'filters' => [
                'search' => $request->query('search'),
                'jenis_kelamin' => $request->query('jenis_kelamin'),
            ],
            'summary' => $result['summary'],
        ]);
    }

    /**
     * Form tambah pemilih.
     */
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $relawans = AnggotaTim::query()
            ->where('desa_id', $user->desa_id)
            ->where('role', 'relawan')
            ->get()
            ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->map(fn($r) => [
                'id' => $r->id,
                'nama' => $r->nama,
            ]);

        return Inertia::render('desa/PemilihForm', [
            'mode' => 'create',
            'desa' => $user->desa->nama,
            'relawans' => $relawans,
        ]);
    }

    /**
     * Simpan pemilih baru.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'nik' => ['required', 'digits:16'],
            'nama' => ['required', 'string', 'regex:/^[a-zA-Z\s\.\'­-]+$/', 'max:100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat' => ['required', 'string', 'regex:/^[a-zA-Z0-9\s\.,\/#-]+$/', 'max:255'],
            'rt' => ['required', 'regex:/^[0-9]+$/', 'max:5'],
            'rw' => ['required', 'regex:/^[0-9]+$/', 'max:5'],
            'relawan_id' => ['required', 'exists:anggota_tim,id'],
        ], [
            'nama.regex' => "Nama hanya boleh berisi huruf, spasi, titik, koma atas ('), dan tanda hubung (-).",
            'alamat.regex' => 'Alamat hanya boleh berisi huruf, angka, spasi, titik, koma, garis miring (/), tanda pagar (#), dan tanda hubung (-).',
            'rt.regex' => 'RT hanya boleh berisi angka.',
            'rw.regex' => 'RW hanya boleh berisi angka.',
        ]);

        $nikHash = hash('sha256', $data['nik']);

        if (DB::table('pemilihs')->where('nik_hash', $nikHash)->exists()) {
            return back()->withErrors(['nik' => 'NIK sudah terdaftar dalam sistem.']);
        }

        $pemilih = Pemilih::create([
            'nik' => $data['nik'],
            'nik_hash' => $nikHash,
            'nama' => $data['nama'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'alamat' => $data['alamat'],
            'rt' => $data['rt'],
            'rw' => $data['rw'],
            'desa_id' => $user->desa_id,
            'kecamatan_id' => $user->kecamatan_id,
            'user_id' => $user->id,
            'relawan_id' => $data['relawan_id'] ?? null,
        ]);

        activity()
            ->performedOn($pemilih)
            ->event('created')
            ->log("Menambahkan pemilih baru: {$data['nama']}");

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil ditambahkan.');
    }

    /**
     * Form edit pemilih.
     */
    public function edit(Request $request, Pemilih $pemilih): Response
    {
        /** @var User $user */
        $user = $request->user();
        abort_if($pemilih->desa_id !== $user->desa_id, 403);

        $relawans = AnggotaTim::query()
            ->where('desa_id', $user->desa_id)
            ->where('role', 'relawan')
            ->get()
            ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->map(fn($r) => [
                'id' => $r->id,
                'nama' => $r->nama,
            ]);

        return Inertia::render('desa/PemilihForm', [
            'mode' => 'edit',
            'desa' => $user->desa->nama,
            'pemilih' => [
                'id' => $pemilih->id,
                'nik' => $pemilih->nik,
                'nama' => $pemilih->nama,
                'jenis_kelamin' => $pemilih->jenis_kelamin,
                'alamat' => $pemilih->alamat,
                'rt' => $pemilih->rt,
                'rw' => $pemilih->rw,
                'relawan_id' => $pemilih->relawan_id,
            ],
            'relawans' => $relawans,
        ]);
    }

    /**
     * Update data pemilih.
     */
    public function update(Request $request, Pemilih $pemilih): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if($pemilih->desa_id !== $user->desa_id, 403);

        $data = $request->validate([
            'nik' => ['required', 'digits:16'],
            'nama' => ['required', 'string', 'regex:/^[a-zA-Z\s\.\'­-]+$/', 'max:100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat' => ['required', 'string', 'regex:/^[a-zA-Z0-9\s\.,\/#-]+$/', 'max:255'],
            'rt' => ['required', 'regex:/^[0-9]+$/', 'max:5'],
            'rw' => ['required', 'regex:/^[0-9]+$/', 'max:5'],
            'relawan_id' => ['required', 'exists:anggota_tim,id'],
        ], [
            'nama.regex' => "Nama hanya boleh berisi huruf, spasi, titik, koma atas ('), dan tanda hubung (-).",
            'alamat.regex' => 'Alamat hanya boleh berisi huruf, angka, spasi, titik, koma, garis miring (/), tanda pagar (#), dan tanda hubung (-).',
            'rt.regex' => 'RT hanya boleh berisi angka.',
            'rw.regex' => 'RW hanya boleh berisi angka.',
        ]);

        $nikHash = hash('sha256', $data['nik']);

        if (DB::table('pemilihs')->where('nik_hash', $nikHash)->where('id', '!=', $pemilih->id)->exists()) {
            return back()->withErrors(['nik' => 'NIK sudah terdaftar untuk pemilih lain.']);
        }

        $pemilih->nik = $data['nik'];
        $pemilih->nik_hash = $nikHash;
        $pemilih->nama = $data['nama'];
        $pemilih->jenis_kelamin = $data['jenis_kelamin'];
        $pemilih->alamat = $data['alamat'];
        $pemilih->rt = $data['rt'];
        $pemilih->rw = $data['rw'];
        $pemilih->relawan_id = $data['relawan_id'] ?? null;
        $pemilih->save();

        activity()
            ->performedOn($pemilih)
            ->event('updated')
            ->log("Mengubah data pemilih: {$data['nama']}");

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil diperbarui.');
    }

    /**
     * Hapus data pemilih.
     */
    public function destroy(Request $request, Pemilih $pemilih): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if($pemilih->desa_id !== $user->desa_id, 403);

        activity()
            ->performedOn($pemilih)
            ->event('deleted')
            ->log("Menghapus data pemilih: {$pemilih->nama}");

        DB::table('pemilihs')->where('id', $pemilih->id)->delete();

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil dihapus.');
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

        if ($search && is_numeric($search)) {
            $query->where('nik_hash', hash('sha256', $search));
        }

        $formatRow = fn ($p) => array_filter([
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

        if ($search && ! is_numeric($search)) {
            // In-memory name search (required because nama is encrypted)
            $all = $query->get()->filter(
                fn ($p) => str_contains(strtolower($p->nama), strtolower($search))
            );

            $countTotal = $all->count();
            $countL = $all->where('jenis_kelamin', 'L')->count();
            $countP = $all->where('jenis_kelamin', 'P')->count();
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
            $paginated = $query->paginate($perPage)->through($formatRow);
        }

        return [
            'paginated' => $paginated,
            'summary' => [
                'total' => $countTotal,
                'l' => $countL,
                'p' => $countP,
            ],
        ];
    }
}
