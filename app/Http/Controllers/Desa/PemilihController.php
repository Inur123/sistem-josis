<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use App\Models\Pemilih;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $user  = $request->user();
        $query = Pemilih::where('desa_id', $user->desa_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Dekripsi untuk ditampilkan (dilakukan otomatis via cast)
        $pemilihs = $query->through(fn ($p) => [
            'id'            => $p->id,
            'nik'           => $p->nik,
            'nama'          => $p->nama,
            'jenis_kelamin' => $p->jenis_kelamin,
            'alamat'        => $p->alamat,
            'rt'            => $p->rt,
            'rw'            => $p->rw,
            'created_at'    => $p->created_at->format('d/m/Y'),
        ]);

        return Inertia::render('desa/Pemilih', [
            'pemilihs' => $pemilihs,
            'desa'     => $user->desa->nama,
        ]);
    }

    /**
     * Form tambah pemilih.
     */
    public function create(Request $request): Response
    {
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
        $user = $request->user();

        $data = $request->validate([
            'nik'           => ['required', 'digits:16'],
            'nama'          => ['required', 'string', 'max:100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat'        => ['required', 'string', 'max:255'],
            'rt'            => ['required', 'string', 'max:5'],
            'rw'            => ['required', 'string', 'max:5'],
        ]);

        // Cek duplikat NIK via hash (tanpa membuka enkripsi)
        $nikHash = hash('sha256', $data['nik']);

        if (Pemilih::where('nik_hash', $nikHash)->exists()) {
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
        $user = $request->user();

        // Pastikan pemilih milik desa yang sama
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
        $user = $request->user();
        abort_if($pemilih->desa_id !== $user->desa_id, 403);

        $data = $request->validate([
            'nik'           => ['required', 'digits:16'],
            'nama'          => ['required', 'string', 'max:100'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat'        => ['required', 'string', 'max:255'],
            'rt'            => ['required', 'string', 'max:5'],
            'rw'            => ['required', 'string', 'max:5'],
        ]);

        $nikHash = hash('sha256', $data['nik']);

        // Cek duplikat NIK (kecuali milik dirinya sendiri)
        if (Pemilih::where('nik_hash', $nikHash)->where('id', '!=', $pemilih->id)->exists()) {
            return back()->withErrors(['nik' => 'NIK sudah terdaftar untuk pemilih lain.']);
        }

        $pemilih->update([
            'nik'           => $data['nik'],
            'nik_hash'      => $nikHash,
            'nama'          => $data['nama'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'alamat'        => $data['alamat'],
            'rt'            => $data['rt'],
            'rw'            => $data['rw'],
        ]);

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil diperbarui.');
    }

    /**
     * Hapus data pemilih.
     */
    public function destroy(Request $request, Pemilih $pemilih): RedirectResponse
    {
        $user = $request->user();
        abort_if($pemilih->desa_id !== $user->desa_id, 403);

        $pemilih->delete();

        return redirect()->route('desa.pemilih.index')
            ->with('success', 'Data pemilih berhasil dihapus.');
    }
}
