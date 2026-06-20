<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AkunService
{
    /**
     * Get all users with their relations, formatted for the frontend.
     *
     * @return \Illuminate\Support\Collection<int, array<string,mixed>>
     */
    public function getAllFormatted(): \Illuminate\Support\Collection
    {
        /** @var \Illuminate\Database\Eloquent\Builder<User> $query */
        $query = User::query();

        return $query->with(['kecamatan', 'desa'])->get()->map(fn ($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'email'        => $u->email,
            'role'         => $u->role,
            'kecamatan_id' => $u->kecamatan_id,
            'kecamatan'    => $u->kecamatan?->nama,
            'desa_id'      => $u->desa_id,
            'desa'         => $u->desa?->nama,
        ]);
    }

    /**
     * Update an existing user account.
     *
     * @param  User  $user
     * @param  array<string,mixed>  $data  validated data
     * @return array{error: string}|null  returns error or null on success
     */
    public function update(User $user, array $data): ?array
    {
        $emailHash = hash('sha256', strtolower(trim($data['email'])));

        if (DB::table('users')->where('email_hash', $emailHash)->where('id', '!=', $user->id)->exists()) {
            return ['email' => 'Email sudah terdaftar untuk pengguna lain.'];
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

        return null;
    }

    /**
     * Delete a user account. Prevents self-deletion.
     *
     * @param  User  $user
     * @return array{error: string}|null
     */
    public function destroy(User $user): ?array
    {
        if ($user->id === Auth::id()) {
            return ['error' => 'Anda tidak dapat menghapus akun Anda sendiri.'];
        }

        DB::table('users')->where('id', $user->id)->delete();

        return null;
    }
}
