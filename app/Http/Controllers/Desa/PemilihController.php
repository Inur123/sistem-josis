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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        if ($request->header('X-Requested-With') === 'XMLHttpRequest' && ! $request->header('X-Inertia')) {
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

        return Inertia::render('desa/pemilih/Index', [
            'pemilihs' => $result['paginated'],
            'desa' => $user->desa->nama,
            'filters' => [
                'search' => $request->query('search'),
                'jenis_kelamin' => $request->query('jenis_kelamin'),
                'status' => $request->query('status'),
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
            ->map(fn ($r) => [
                'id' => $r->id,
                'nama' => $r->nama,
            ]);

        return Inertia::render('desa/pemilih/Form', [
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
            'foto_ktp' => ['required', 'image', 'max:10240'],
        ], [
            'nama.regex' => "Nama hanya boleh berisi huruf, spasi, titik, koma atas ('), dan tanda hubung (-).",
            'alamat.regex' => 'Alamat hanya boleh berisi huruf, angka, spasi, titik, koma, garis miring (/), tanda pagar (#), dan tanda hubung (-).',
            'rt.regex' => 'RT hanya boleh berisi angka.',
            'rw.regex' => 'RW hanya boleh berisi angka.',
            'foto_ktp.required' => 'Foto KTP wajib diunggah.',
            'foto_ktp.image' => 'File harus berupa gambar.',
            'foto_ktp.max' => 'Ukuran file gambar maksimal 10MB.',
        ]);

        $nikHash = hash('sha256', $data['nik']);

        if (DB::table('pemilihs')->where('nik_hash', $nikHash)->exists()) {
            return back()->withErrors(['nik' => 'NIK sudah terdaftar dalam sistem.']);
        }

        $fotoKtpPath = null;
        if ($request->hasFile('foto_ktp')) {
            $file = $request->file('foto_ktp');
            $fileContents = file_get_contents($file->getRealPath());
            $encryptedContents = Crypt::encrypt($fileContents);
            $filename = Str::uuid().'.enc';
            $fotoKtpPath = 'ktps/'.$filename;
            Storage::put('private/'.$fotoKtpPath, $encryptedContents);
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
            'foto_ktp' => $fotoKtpPath,
        ]);

        activity()
            ->performedOn($pemilih)
            ->event('created')
            ->log("Menambahkan pemilih baru: {$data['nama']}");

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil ditambahkan.');
    }

    /**
     * Detail pemilih.
     */
    public function show(Request $request, Pemilih $pemilih): Response
    {
        /** @var User $user */
        $user = $request->user();
        abort_if($pemilih->desa_id !== $user->desa_id, 403);

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
            ->map(fn ($r) => [
                'id' => $r->id,
                'nama' => $r->nama,
            ]);

        return Inertia::render('desa/pemilih/Form', [
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
                'foto_ktp' => $pemilih->foto_ktp ? route('pemilih.ktp', $pemilih->id) : null,
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
            'foto_ktp' => ['nullable', 'image', 'max:10240'],
        ], [
            'nama.regex' => "Nama hanya boleh berisi huruf, spasi, titik, koma atas ('), dan tanda hubung (-).",
            'alamat.regex' => 'Alamat hanya boleh berisi huruf, angka, spasi, titik, koma, garis miring (/), tanda pagar (#), dan tanda hubung (-).',
            'rt.regex' => 'RT hanya boleh berisi angka.',
            'rw.regex' => 'RW hanya boleh berisi angka.',
            'foto_ktp.image' => 'File harus berupa gambar.',
            'foto_ktp.max' => 'Ukuran file gambar maksimal 10MB.',
        ]);

        $nikHash = hash('sha256', $data['nik']);

        if (DB::table('pemilihs')->where('nik_hash', $nikHash)->where('id', '!=', $pemilih->id)->exists()) {
            return back()->withErrors(['nik' => 'NIK sudah terdaftar untuk pemilih lain.']);
        }

        if ($request->hasFile('foto_ktp')) {
            if ($pemilih->foto_ktp && Storage::exists('private/'.$pemilih->foto_ktp)) {
                Storage::delete('private/'.$pemilih->foto_ktp);
            }

            $file = $request->file('foto_ktp');
            $fileContents = file_get_contents($file->getRealPath());
            $encryptedContents = Crypt::encrypt($fileContents);
            $filename = Str::uuid().'.enc';
            $fotoKtpPath = 'ktps/'.$filename;
            Storage::put('private/'.$fotoKtpPath, $encryptedContents);
            $pemilih->foto_ktp = $fotoKtpPath;
        }

        $pemilih->nik = $data['nik'];
        $pemilih->nik_hash = $nikHash;
        $pemilih->nama = $data['nama'];
        $pemilih->jenis_kelamin = $data['jenis_kelamin'];
        $pemilih->alamat = $data['alamat'];
        $pemilih->rt = $data['rt'];
        $pemilih->rw = $data['rw'];
        $pemilih->relawan_id = $data['relawan_id'] ?? null;

        // Reset status to pending verification upon editing
        $pemilih->status = 'belum_verifikasi';
        $pemilih->alasan_ditolak = null;

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

        if ($pemilih->foto_ktp && Storage::exists('private/'.$pemilih->foto_ktp)) {
            Storage::delete('private/'.$pemilih->foto_ktp);
        }

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
                'foto_ktp' => $p->foto_ktp ? route('pemilih.ktp', $p->id) : null,
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
}
