<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AkunController extends Controller
{
    public function index(Request $request): Response
    {
        // Harus pakai Eloquent agar cast AES-256-GCM (name, email) terdekripsi
        /** @var \Illuminate\Database\Eloquent\Builder<User> $userQuery */
        $userQuery = User::query();
        $users = $userQuery->with(['kecamatan', 'desa'])->get()->map(fn ($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'email'        => $u->email,
            'role'         => $u->role,
            'kecamatan_id' => $u->kecamatan_id,
            'kecamatan'    => $u->kecamatan?->nama,
            'desa_id'      => $u->desa_id,
            'desa'         => $u->desa?->nama,
        ]);

        /** @var \Illuminate\Database\Eloquent\Builder<Kecamatan> $kecamatanQuery */
        $kecamatanQuery = Kecamatan::query();

        /** @var \Illuminate\Database\Eloquent\Builder<Desa> $desaQuery */
        $desaQuery = Desa::query();

        return Inertia::render('admin/Akun', [
            'users'      => $users,
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

        $emailHash = hash('sha256', strtolower(trim($data['email'])));

        if (DB::table('users')->where('email_hash', $emailHash)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['email' => 'Email sudah terdaftar untuk pengguna lain.']);
        }

        $user->name         = $data['name'];
        $user->email        = $data['email'];
        $user->email_hash   = $emailHash;
        $user->role         = $data['role'];
        $user->kecamatan_id = $data['role'] === 'admin' ? null : $data['kecamatan_id'];
        $user->desa_id      = $data['role'] === 'desa' ? $data['desa_id'] : null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        DB::table('users')->where('id', $user->id)->delete();

        return back()->with('success', 'Akun berhasil dihapus.');
    }
}
