<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use App\Models\Pemilih;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PemilihController extends Controller
{
    /**
     * Daftar pemilih di desa user yang login.
     */
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user   = $request->user();
        $desaId = $user->desa_id;
        $desaNama = $user->desa->nama;

        $search = $request->query('search');
        $jenisKelamin = $request->query('jenis_kelamin');

        // Pakai Eloquent agar cast enkripsi berjalan
        /** @var \Illuminate\Database\Eloquent\Builder<\App\Models\Pemilih> $query */
        $query = Pemilih::query()->where('desa_id', $desaId);

        if ($jenisKelamin) {
            $query->where('jenis_kelamin', $jenisKelamin);
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
                    'created_at'    => $p->created_at?->format('d/m/Y'),
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
                'created_at'    => $p->created_at?->format('d/m/Y'),
            ]);
        }

        return Inertia::render('desa/Pemilih', [
            'pemilihs' => $paginated,
            'desa'     => $desaNama,
            'filters'  => [
                'search'        => $search,
                'jenis_kelamin' => $jenisKelamin,
            ],
            'summary'  => [
                'total' => $countTotal,
                'l'     => $countLakiLaki,
                'p'     => $countPerempuan,
            ],
        ]);
    }

    /**
     * Form tambah pemilih.
     */
    public function create(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user     = $request->user();
        $desaNama = $user->desa->nama;

        return Inertia::render('desa/PemilihForm', [
            'mode' => 'create',
            'desa' => $desaNama,
        ]);
    }

    /**
     * Simpan pemilih baru.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $data = $request->validate([
            'nik'           => ['required', 'digits:16'],
            'nama'          => ['required', 'string', 'regex:/^[a-zA-Z\s\.\'-]+$/', 'max:100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat'        => ['required', 'string', 'regex:/^[a-zA-Z0-9\s\.,\/#-]+$/', 'max:255'],
            'rt'            => ['required', 'regex:/^[0-9]+$/', 'max:5'],
            'rw'            => ['required', 'regex:/^[0-9]+$/', 'max:5'],
        ], [
            'nama.regex'   => 'Nama hanya boleh berisi huruf, spasi, titik, koma atas (\'), dan tanda hubung (-).',
            'alamat.regex' => 'Alamat hanya boleh berisi huruf, angka, spasi, titik, koma, garis miring (/), tanda pagar (#), dan tanda hubung (-).',
            'rt.regex'     => 'RT hanya boleh berisi angka.',
            'rw.regex'     => 'RW hanya boleh berisi angka.',
        ]);

        $nikHash = hash('sha256', $data['nik']);

        if (DB::table('pemilihs')->where('nik_hash', $nikHash)->exists()) {
            return back()->withErrors(['nik' => 'NIK sudah terdaftar dalam sistem.']);
        }

        Pemilih::create([
            'nik'           => $data['nik'],
            'nik_hash'      => $nikHash,
            'nama'          => $data['nama'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'alamat'        => $data['alamat'],
            'rt'            => $data['rt'],
            'rw'            => $data['rw'],
            'desa_id'       => $user->desa_id,
            'kecamatan_id'  => $user->kecamatan_id,
            'user_id'       => $user->id,
        ]);

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil ditambahkan.');
    }

    /**
     * Form edit pemilih.
     */
    public function edit(Request $request, Pemilih $pemilih): Response
    {
        /** @var \App\Models\User $user */
        $user     = $request->user();
        abort_if($pemilih->desa_id !== $user->desa_id, 403);

        $desaNama = $user->desa->nama;

        return Inertia::render('desa/PemilihForm', [
            'mode'    => 'edit',
            'desa'    => $desaNama,
            'pemilih' => [
                'id'            => $pemilih->id,
                'nik'           => $pemilih->nik,
                'nama'          => $pemilih->nama,
                'jenis_kelamin' => $pemilih->jenis_kelamin,
                'alamat'        => $pemilih->alamat,
                'rt'            => $pemilih->rt,
                'rw'            => $pemilih->rw,
            ],
        ]);
    }

    /**
     * Update data pemilih.
     */
    public function update(Request $request, Pemilih $pemilih): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        abort_if($pemilih->desa_id !== $user->desa_id, 403);

        $data = $request->validate([
            'nik'           => ['required', 'digits:16'],
            'nama'          => ['required', 'string', 'regex:/^[a-zA-Z\s\.\'-]+$/', 'max:100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat'        => ['required', 'string', 'regex:/^[a-zA-Z0-9\s\.,\/#-]+$/', 'max:255'],
            'rt'            => ['required', 'regex:/^[0-9]+$/', 'max:5'],
            'rw'            => ['required', 'regex:/^[0-9]+$/', 'max:5'],
        ], [
            'nama.regex'   => 'Nama hanya boleh berisi huruf, spasi, titik, koma atas (\'), dan tanda hubung (-).',
            'alamat.regex' => 'Alamat hanya boleh berisi huruf, angka, spasi, titik, koma, garis miring (/), tanda pagar (#), dan tanda hubung (-).',
            'rt.regex'     => 'RT hanya boleh berisi angka.',
            'rw.regex'     => 'RW hanya boleh berisi angka.',
        ]);

        $nikHash = hash('sha256', $data['nik']);

        if (DB::table('pemilihs')->where('nik_hash', $nikHash)->where('id', '!=', $pemilih->id)->exists()) {
            return back()->withErrors(['nik' => 'NIK sudah terdaftar untuk pemilih lain.']);
        }

        $pemilih->nik           = $data['nik'];
        $pemilih->nik_hash      = $nikHash;
        $pemilih->nama          = $data['nama'];
        $pemilih->jenis_kelamin = $data['jenis_kelamin'];
        $pemilih->alamat        = $data['alamat'];
        $pemilih->rt            = $data['rt'];
        $pemilih->rw            = $data['rw'];
        $pemilih->save();

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil diperbarui.');
    }

    /**
     * Hapus data pemilih.
     */
    public function destroy(Request $request, Pemilih $pemilih): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        abort_if($pemilih->desa_id !== $user->desa_id, 403);

        DB::table('pemilihs')->where('id', $pemilih->id)->delete();

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil dihapus.');
    }
}
