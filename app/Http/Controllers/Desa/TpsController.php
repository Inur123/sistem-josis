<?php

namespace App\Http\Controllers\Desa;

use App\Events\TpsChanged;
use App\Http\Controllers\Controller;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TpsController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $tpsList = Tps::query()->where('desa_id', $user->desa_id)
            ->orderBy('nama', 'asc')
            ->get()
            ->map(fn(Tps $tps): array => [
                'id'         => $tps->id,
                'nama'       => $tps->nama,
                'created_at' => $tps->created_at ? $tps->created_at->format('d/m/Y') : null,
            ])
            ->toArray();

        return Inertia::render('desa/tps/Index', [
            'tpsList'   => $tpsList,
            'totalTps'  => count($tpsList),
            'desa'      => $user->desa?->nama,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('tps', 'nama')->where('desa_id', $user->desa_id),
            ],
        ], [
            'nama.required' => 'Nama TPS wajib diisi.',
            'nama.unique'   => 'Nama TPS sudah terdaftar di desa ini.',
            'nama.max'      => 'Nama TPS maksimal 100 karakter.',
        ]);

        $tps = Tps::query()->create([
            'nama'    => $data['nama'],
            'desa_id' => $user->desa_id,
        ]);

        activity()
            ->performedOn($tps)
            ->event('created')
            ->log("Menambahkan TPS baru: {$tps->nama} (Desa {$user->desa?->nama})");

        // Load desa for realtime channel
        $tps->load('desa');
        broadcast(new TpsChanged($tps, 'created'))->toOthers();

        return back()->with('success', 'TPS berhasil ditambahkan.');
    }

    public function update(Request $request, Tps $tps): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($tps->desa_id !== $user->desa_id, 403, 'Akses ditolak.');

        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('tps', 'nama')
                    ->where('desa_id', $user->desa_id)
                    ->ignore($tps->id),
            ],
        ], [
            'nama.required' => 'Nama TPS wajib diisi.',
            'nama.unique'   => 'Nama TPS sudah terdaftar di desa ini.',
        ]);

        $oldNama = $tps->nama;
        $tps->update(['nama' => $data['nama']]);

        activity()
            ->performedOn($tps)
            ->event('updated')
            ->log("Mengubah nama TPS: {$oldNama} -> {$tps->nama} (Desa {$user->desa?->nama})");

        $tps->load('desa');
        broadcast(new TpsChanged($tps, 'updated'))->toOthers();

        return back()->with('success', 'TPS berhasil diperbarui.');
    }

    public function destroy(Request $request, Tps $tps): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($tps->desa_id !== $user->desa_id, 403, 'Akses ditolak.');

        // Load desa before delete for broadcast channel
        $tps->load('desa');
        $tpsData = clone $tps;
        $tpsNama = $tps->nama;
        $desaNama = $user->desa?->nama;

        // Delete C-Hasil file if exists
        if ($tps->dataSuara && $tps->dataSuara->c_hasil_path) {
            \Illuminate\Support\Facades\Storage::delete('private/' . $tps->dataSuara->c_hasil_path);
        }

        Tps::destroy($tps->id); // DataSuara cascade deleted by DB

        activity()
            ->event('deleted')
            ->log("Menghapus TPS: {$tpsNama} (Desa {$desaNama})");

        broadcast(new TpsChanged($tpsData, 'deleted'))->toOthers();

        return back()->with('success', 'TPS berhasil dihapus.');
    }
}
