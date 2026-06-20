<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use App\Models\Pemilih;
use App\Services\PemilihService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PemilihController extends Controller
{
    public function __construct(private PemilihService $pemilihService) {}

    /**
     * Daftar pemilih di desa user yang login.
     */
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $result = $this->pemilihService->paginate(
            $request,
            scope: ['desa_id' => $user->desa_id],
        );

        return Inertia::render('desa/Pemilih', [
            'pemilihs' => $result['paginated'],
            'desa'     => $user->desa->nama,
            'filters'  => [
                'search'        => $request->query('search'),
                'jenis_kelamin' => $request->query('jenis_kelamin'),
            ],
            'summary'  => $result['summary'],
        ]);
    }

    /**
     * Form tambah pemilih.
     */
    public function create(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return Inertia::render('desa/PemilihForm', [
            'mode' => 'create',
            'desa' => $user->desa->nama,
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
            'nama'          => ['required', 'string', 'regex:/^[a-zA-Z\s\.\'­-]+$/', 'max:100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat'        => ['required', 'string', 'regex:/^[a-zA-Z0-9\s\.,\/#-]+$/', 'max:255'],
            'rt'            => ['required', 'regex:/^[0-9]+$/', 'max:5'],
            'rw'            => ['required', 'regex:/^[0-9]+$/', 'max:5'],
        ], [
            'nama.regex'   => "Nama hanya boleh berisi huruf, spasi, titik, koma atas ('), dan tanda hubung (-).",
            'alamat.regex' => 'Alamat hanya boleh berisi huruf, angka, spasi, titik, koma, garis miring (/), tanda pagar (#), dan tanda hubung (-).',
            'rt.regex'     => 'RT hanya boleh berisi angka.',
            'rw.regex'     => 'RW hanya boleh berisi angka.',
        ]);

        $error = $this->pemilihService->store($data, $user->desa_id, $user->kecamatan_id, $user->id);

        if ($error) {
            return back()->withErrors($error);
        }

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil ditambahkan.');
    }

    /**
     * Form edit pemilih.
     */
    public function edit(Request $request, Pemilih $pemilih): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        abort_if($pemilih->desa_id !== $user->desa_id, 403);

        return Inertia::render('desa/PemilihForm', [
            'mode'    => 'edit',
            'desa'    => $user->desa->nama,
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
            'nama'          => ['required', 'string', 'regex:/^[a-zA-Z\s\.\'­-]+$/', 'max:100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat'        => ['required', 'string', 'regex:/^[a-zA-Z0-9\s\.,\/#-]+$/', 'max:255'],
            'rt'            => ['required', 'regex:/^[0-9]+$/', 'max:5'],
            'rw'            => ['required', 'regex:/^[0-9]+$/', 'max:5'],
        ], [
            'nama.regex'   => "Nama hanya boleh berisi huruf, spasi, titik, koma atas ('), dan tanda hubung (-).",
            'alamat.regex' => 'Alamat hanya boleh berisi huruf, angka, spasi, titik, koma, garis miring (/), tanda pagar (#), dan tanda hubung (-).',
            'rt.regex'     => 'RT hanya boleh berisi angka.',
            'rw.regex'     => 'RW hanya boleh berisi angka.',
        ]);

        $error = $this->pemilihService->update($pemilih, $data);

        if ($error) {
            return back()->withErrors($error);
        }

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

        $this->pemilihService->destroy($pemilih);

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil dihapus.');
    }
}
