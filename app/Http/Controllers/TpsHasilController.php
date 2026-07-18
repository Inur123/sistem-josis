<?php

namespace App\Http\Controllers;

use App\Models\DataSuara;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class TpsHasilController extends Controller
{
    /**
     * Decrypt and serve the C-Hasil photo securely.
     * Identik dengan PemilihKtpController.
     */
    public function show(Request $request, Tps $tps): Response
    {
        /** @var User $user */
        $user = $request->user();

        // Role-based authorization
        if ($user->role === 'desa') {
            abort_if($tps->desa_id !== $user->desa_id, 403, 'Akses ditolak.');
        } elseif ($user->role === 'kecamatan') {
            $tps->load('desa');
            abort_if($tps->desa?->kecamatan_id !== $user->kecamatan_id, 403, 'Akses ditolak.');
        } elseif ($user->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $dataSuara = DataSuara::where('tps_id', $tps->id)->first();

        if (! $dataSuara || ! $dataSuara->c_hasil_path) {
            abort(404, 'C-Hasil tidak ditemukan.');
        }

        $storagePath = 'private/' . $dataSuara->c_hasil_path;

        if (! Storage::exists($storagePath)) {
            abort(404, 'File C-Hasil tidak ditemukan.');
        }

        $encryptedContents = Storage::get($storagePath);

        try {
            $decryptedContents = Crypt::decrypt($encryptedContents);
        } catch (DecryptException $e) {
            abort(500, 'Gagal mendekripsi C-Hasil.');
        }

        return response($decryptedContents)
            ->header('Content-Type', 'image/jpeg')
            ->header('Cache-Control', 'private, max-age=86400');
    }
}
