<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\User;
use App\Services\AkunService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AkunController extends Controller
{
    public function __construct(private AkunService $akunService) {}

    public function index(Request $request): Response
    {
        /** @var \Illuminate\Database\Eloquent\Builder<Kecamatan> $kecamatanQuery */
        $kecamatanQuery = Kecamatan::query();

        /** @var \Illuminate\Database\Eloquent\Builder<Desa> $desaQuery */
        $desaQuery = Desa::query();

        return Inertia::render('admin/Akun', [
            'users'      => $this->akunService->getAllFormatted(),
            'kecamatans' => $kecamatanQuery->orderBy('nama', 'asc')->get(['id', 'nama']),
            'desas'      => $desaQuery->orderBy('nama', 'asc')->get(['id', 'nama', 'kecamatan_id']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255'],
            'password'     => ['nullable', 'string', 'min:8'],
            'role'         => ['required', Rule::in(['admin', 'kecamatan', 'desa'])],
            'kecamatan_id' => ['required_if:role,kecamatan,desa', 'nullable', 'exists:kecamatans,id'],
            'desa_id'      => ['required_if:role,desa', 'nullable', 'exists:desas,id'],
        ]);

        $error = $this->akunService->update($user, $data);

        if ($error) {
            return back()->withErrors($error);
        }

        return back()->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $error = $this->akunService->destroy($user);

        if ($error) {
            return back()->withErrors($error);
        }

        return back()->with('success', 'Akun berhasil dihapus.');
    }
}
