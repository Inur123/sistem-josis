<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AkunController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::with(['kecamatan', 'desa'])
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'kecamatan_id' => $u->kecamatan_id,
                'kecamatan' => $u->kecamatan?->nama,
                'desa_id' => $u->desa_id,
                'desa' => $u->desa?->nama,
            ]);

        return Inertia::render('admin/Akun', [
            'users' => $users,
            'kecamatans' => Kecamatan::orderBy('nama')->get(['id', 'nama']),
            'desas' => Desa::orderBy('nama')->get(['id', 'nama', 'kecamatan_id']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'kecamatan', 'desa'])],
            'kecamatan_id' => ['required_if:role,kecamatan,desa', 'nullable', 'exists:kecamatans,id'],
            'desa_id' => ['required_if:role,desa', 'nullable', 'exists:desas,id'],
        ]);

        $emailHash = hash('sha256', strtolower(trim($data['email'])));

        if (User::where('email_hash', $emailHash)->exists()) {
            return back()->withErrors(['email' => 'Email sudah terdaftar.']);
        }

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_hash' => $emailHash,
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'kecamatan_id' => $data['role'] === 'admin' ? null : $data['kecamatan_id'],
            'desa_id' => $data['role'] === 'desa' ? $data['desa_id'] : null,
        ]);

        return back()->with('success', 'Akun berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'kecamatan', 'desa'])],
            'kecamatan_id' => ['required_if:role,kecamatan,desa', 'nullable', 'exists:kecamatans,id'],
            'desa_id' => ['required_if:role,desa', 'nullable', 'exists:desas,id'],
        ]);

        $emailHash = hash('sha256', strtolower(trim($data['email'])));

        if (User::where('email_hash', $emailHash)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['email' => 'Email sudah terdaftar untuk pengguna lain.']);
        }

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'email_hash' => $emailHash,
            'role' => $data['role'],
            'kecamatan_id' => $data['role'] === 'admin' ? null : $data['kecamatan_id'],
            'desa_id' => $data['role'] === 'desa' ? $data['desa_id'] : null,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return back()->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $user->delete();

        return back()->with('success', 'Akun berhasil dihapus.');
    }
}
