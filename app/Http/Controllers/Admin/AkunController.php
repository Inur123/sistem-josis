<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserChanged;
use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
        $users = User::with(['kecamatan', 'desa'])->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'kecamatan_id' => $u->kecamatan_id,
            'kecamatan' => $u->kecamatan?->nama,
            'desa_id' => $u->desa_id,
            'desa' => $u->desa?->nama,
        ]);

        return Inertia::render('admin/akun/Index', [
            'users' => $users,
            'kecamatans' => Kecamatan::orderBy('nama', 'asc')->select('id', 'nama')->get(),
            'desas' => Desa::orderBy('nama', 'asc')->select('id', 'nama', 'kecamatan_id')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
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

        if (DB::table('users')->where('email_hash', $emailHash)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['email' => 'Email sudah terdaftar untuk pengguna lain.']);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->email_hash = $emailHash;
        $user->role = $data['role'];
        $user->kecamatan_id = $data['role'] === 'admin' ? null : $data['kecamatan_id'];
        $user->desa_id = $data['role'] === 'desa' ? $data['desa_id'] : null;

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        activity()
            ->performedOn($user)
            ->event('updated')
            ->log("Mengubah data akun: {$user->email} ({$user->name})");

        UserChanged::dispatch($user, 'updated');

        return back()->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        activity()
            ->performedOn($user)
            ->event('deleted')
            ->log("Menghapus akun: {$user->email} ({$user->name})");

        DB::table('users')->where('id', $user->id)->delete();

        UserChanged::dispatch($user, 'deleted');

        return back()->with('success', 'Akun berhasil dihapus.');
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        set_time_limit(180);

        $tempFile = tempnam(sys_get_temp_dir(), 'export_akun');
        if ($tempFile === false) {
            abort(500, 'Gagal membuat file temporary');
        }

        $writer = new \OpenSpout\Writer\XLSX\Writer();
        $writer->openToFile($tempFile);

        // --- STYLING ---
        $headerStyle = (new \OpenSpout\Common\Entity\Style\Style())
            ->withFontBold(true)
            ->withBackgroundColor('FFFCD116'); // Golkar Yellow

        $titleStyle = (new \OpenSpout\Common\Entity\Style\Style())
            ->withFontBold(true)
            ->withFontSize(14);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('DAFTAR AKUN');
        $sheet->setColumnWidth(6, 1);
        $sheet->setColumnWidth(30, 2); // Nama Operator
        $sheet->setColumnWidth(40, 3); // Email
        $sheet->setColumnWidth(25, 4); // Password
        $sheet->setColumnWidth(15, 5); // Role
        $sheet->setColumnWidth(20, 6); // Kecamatan
        $sheet->setColumnWidth(20, 7); // Desa

        $writer->addRow(\OpenSpout\Common\Entity\Row::fromValuesWithStyle(['DAFTAR AKUN PENGGUNA SISTEM JOSIS'], $titleStyle));
        $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([]));

        $headers = ['NO', 'NAMA OPERATOR', 'EMAIL', 'PASSWORD', 'ROLE', 'KECAMATAN', 'DESA'];
        $writer->addRow(\OpenSpout\Common\Entity\Row::fromValuesWithStyle($headers, $headerStyle));

        $users = User::with(['kecamatan', 'desa'])->get();
        $no = 1;

        foreach ($users as $u) {
            // Tentukan password default berdasarkan role
            $password = match ($u->role) {
                'admin' => 'Admin@Josis2026!',
                'kecamatan' => 'Kecamatan@2026!',
                'desa' => 'Desa@2026!',
                default => '-',
            };

            $rowValues = [
                $no++,
                $u->name,
                $u->email,
                $password,
                strtoupper($u->role),
                $u->kecamatan?->nama ?? '-',
                $u->desa?->nama ?? '-',
            ];

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues($rowValues));
        }

        $writer->close();

        return response()->download($tempFile, 'Daftar_Akun_Josis.xlsx')->deleteFileAfterSend(true);
    }
}
