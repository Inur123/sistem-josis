<?php

namespace App\Http\Controllers;

use App\Models\Pemilih;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PemilihKtpController extends Controller
{
    /**
     * Decrypt and serve the KTP photo securely.
     */
    public function show(Request $request, Pemilih $pemilih): Response
    {
        /** @var User $user */
        $user = $request->user();

        // Role-based authorization check
        if ($user->role === 'desa') {
            abort_if($pemilih->desa_id !== $user->desa_id, 403, 'Akses ditolak.');
        } elseif ($user->role === 'kecamatan') {
            abort_if($pemilih->kecamatan_id !== $user->kecamatan_id, 403, 'Akses ditolak.');
        } elseif ($user->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        // Check if KTP path exists
        if (! $pemilih->foto_ktp || ! Storage::exists('private/'.$pemilih->foto_ktp)) {
            abort(404, 'Foto KTP tidak ditemukan.');
        }

        // Retrieve and decrypt the image
        $encryptedContents = Storage::get('private/'.$pemilih->foto_ktp);
        try {
            $decryptedContents = Crypt::decrypt($encryptedContents);
        } catch (DecryptException $e) {
            abort(500, 'Gagal mendekripsi foto KTP.');
        }

        return response($decryptedContents)
            ->header('Content-Type', 'image/webp')
            ->header('Cache-Control', 'private, max-age=86400');
    }
}
