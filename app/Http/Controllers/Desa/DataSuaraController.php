<?php

namespace App\Http\Controllers\Desa;

use App\Events\DataSuaraChanged;
use App\Http\Controllers\Controller;
use App\Models\DataSuara;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DataSuaraController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $tpsList = Tps::query()
            ->where('desa_id', $user->desa_id)
            ->with('dataSuara')
            ->orderBy('nama')
            ->get()
            ->map(fn(Tps $tps): array => [
                'id'            => $tps->id,
                'nama'          => $tps->nama,
                'total_suara'   => $tps->dataSuara?->total_suara ?? null,
                'has_c_hasil'   => (bool) $tps->dataSuara?->c_hasil_path,
                'c_hasil_url'   => $tps->dataSuara?->c_hasil_path
                    ? route('tps.c-hasil', $tps->id)
                    : null,
                'data_suara_id' => $tps->dataSuara?->id,
                'sudah_diisi'   => $tps->dataSuara !== null,
            ]);

        return Inertia::render('desa/suara/Index', [
            'tpsList' => $tpsList,
            'desa'    => $user->desa?->nama,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'tps_id'      => ['required', 'exists:tps,id'],
            'total_suara' => ['required', 'integer', 'min:0'],
        ], [
            'tps_id.required'      => 'TPS wajib dipilih.',
            'tps_id.exists'        => 'TPS tidak ditemukan.',
            'total_suara.required' => 'Total suara wajib diisi.',
            'total_suara.integer'  => 'Total suara harus berupa angka.',
            'total_suara.min'      => 'Total suara tidak boleh negatif.',
        ]);

        // Pastikan TPS milik desa user
        /** @var Tps $tps */
        $tps = Tps::query()
            ->where('id', $data['tps_id'])
            ->where('desa_id', $user->desa_id)
            ->firstOrFail();

        DataSuara::query()->updateOrCreate(
            ['tps_id' => $tps->id],
            ['total_suara' => $data['total_suara']],
        );

        $desaNama = $user->desa?->nama ?? '-';
        activity()
            ->performedOn($tps)
            ->event('updated')
            ->log("Menyimpan data suara TPS {$tps->nama}: {$data['total_suara']} suara (Desa {$desaNama})");

        $tps->load('desa');
        broadcast(new DataSuaraChanged($tps, 'updated'))->toOthers();

        return back()->with('success', 'Data suara berhasil disimpan.');
    }

    public function uploadCHasil(Request $request, Tps $tps): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($tps->desa_id !== $user->desa_id, 403, 'Akses ditolak.');

        $request->validate([
            'c_hasil' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'c_hasil.required' => 'File C-Hasil wajib diunggah.',
            'c_hasil.uploaded' => 'File gagal diunggah. Kemungkinan ukuran file terlalu besar (maks 5MB) atau koneksi terputus.',
            'c_hasil.image'    => 'File harus berupa gambar (JPEG, JPG, atau PNG).',
            'c_hasil.mimes'    => 'Format file tidak didukung. Gunakan format JPEG, JPG, PNG, atau WebP.',
            'c_hasil.max'      => 'Ukuran file terlalu besar. Maksimal 5MB (file Anda melebihi batas).',
        ]);

        // Ambil atau buat DataSuara
        /** @var DataSuara $dataSuara */
        $dataSuara = DataSuara::query()->firstOrCreate(
            ['tps_id' => $tps->id],
            ['total_suara' => 0],
        );

        // Hapus file lama jika ada
        if ($dataSuara->c_hasil_path && Storage::exists('private/' . $dataSuara->c_hasil_path)) {
            Storage::delete('private/' . $dataSuara->c_hasil_path);
        }

        // Baca & enkripsi (identik dengan pola KTP)
        $file     = $request->file('c_hasil');
        $fileContents      = (string) file_get_contents((string) $file->getRealPath());
        $encryptedContents = Crypt::encrypt($fileContents);
        $filename = Str::uuid() . '.enc';
        $path     = 'c-hasil/' . $filename;
        Storage::put('private/' . $path, $encryptedContents);

        $dataSuara->update(['c_hasil_path' => $path]);

        $desaNama = $user->desa?->nama ?? '-';
        activity()
            ->performedOn($tps)
            ->event('updated')
            ->log("Mengunggah C-Hasil untuk TPS {$tps->nama} (Desa {$desaNama})");

        $tps->load('desa');
        broadcast(new DataSuaraChanged($tps, 'c_hasil_updated'))->toOthers();

        return back()->with('success', 'C-Hasil berhasil diunggah.');
    }
}
