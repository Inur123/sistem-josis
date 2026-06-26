<?php

namespace App\Http\Controllers\Admin;

use App\Events\TeamChanged;
use App\Http\Controllers\Controller;
use App\Models\AnggotaTim;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        /** @var Builder<Kecamatan> $kecQuery */
        $kecQuery = Kecamatan::query();
        $korcams = $kecQuery->with(['anggotaTims' => function ($query) {
            $query->where('role', 'korcam');
        }])->orderBy('nama')->get()->map(function ($kec) {
            $kec->setRelation('anggotaTims', $kec->anggotaTims->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->values());

            return $kec;
        });

        /** @var Builder<Desa> $desaQuery1 */
        $desaQuery1 = Desa::query();
        $kordes = $desaQuery1->with(['kecamatan', 'anggotaTims' => function ($query) {
            $query->where('role', 'kordes');
        }])->get()->map(function ($desa) {
            $desa->setRelation('anggotaTims', $desa->anggotaTims->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->values());

            return $desa;
        })->sortBy([
            ['kecamatan.nama', 'asc'],
            ['nama', 'asc'],
        ])->values();

        /** @var Builder<Desa> $desaQuery2 */
        $desaQuery2 = Desa::query();
        $relawans = $desaQuery2->with(['kecamatan', 'anggotaTims' => function ($query) {
            $query->where('role', 'relawan');
        }])->get()->map(function ($desa) {
            $desa->setRelation('anggotaTims', $desa->anggotaTims->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->values());

            return $desa;
        })->sortBy([
            ['kecamatan.nama', 'asc'],
            ['nama', 'asc'],
        ])->values();

        /** @var Builder<Kecamatan> $kecDropdownQuery */
        $kecDropdownQuery = Kecamatan::query();
        $kecamatans = $kecDropdownQuery->orderBy('nama')->get(['id', 'nama']);

        /** @var Builder<Desa> $desaDropdownQuery */
        $desaDropdownQuery = Desa::query();
        $desas = $desaDropdownQuery->orderBy('nama')->get(['id', 'nama', 'kecamatan_id']);

        return Inertia::render('admin/Tim', [
            'korcams' => $korcams,
            'kordes' => $kordes,
            'relawans' => $relawans,
            'kecamatans' => $kecamatans,
            'desas' => $desas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:korcam,kordes,relawan',
            'kecamatan_id' => 'required_if:role,korcam|nullable|exists:kecamatans,id',
            'desa_id' => 'required_if:role,kordes,relawan|nullable|exists:desas,id',
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        if ($validated['role'] !== 'korcam' && ! empty($validated['desa_id'])) {
            $desa = Desa::query()->whereKey($validated['desa_id'])->first();
            if ($desa) {
                $validated['kecamatan_id'] = $desa->kecamatan_id;
            }
        } else {
            $validated['desa_id'] = null;
        }

        $anggota = AnggotaTim::create($validated);

        activity()
            ->performedOn($anggota)
            ->event('created')
            ->log("Menambahkan anggota tim: {$anggota->nama} ({$validated['role']})");

        TeamChanged::dispatch($anggota, 'created');

        return redirect()->route('admin.tim.index')
            ->with('success', 'Anggota tim berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnggotaTim $tim): RedirectResponse
    {
        $oldKecamatanId = $tim->kecamatan_id;
        $oldDesaId = $tim->desa_id;

        $validated = $request->validate([
            'role' => 'required|in:korcam,kordes,relawan',
            'kecamatan_id' => 'required_if:role,korcam|nullable|exists:kecamatans,id',
            'desa_id' => 'required_if:role,kordes,relawan|nullable|exists:desas,id',
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        if ($validated['role'] !== 'korcam' && ! empty($validated['desa_id'])) {
            $desa = Desa::query()->whereKey($validated['desa_id'])->first();
            if ($desa) {
                $validated['kecamatan_id'] = $desa->kecamatan_id;
            }
        } else {
            $validated['desa_id'] = null;
        }

        $tim->role = $validated['role'];
        $tim->kecamatan_id = $validated['kecamatan_id'];
        $tim->desa_id = $validated['desa_id'];
        $tim->nama = $validated['nama'];
        $tim->nik = $validated['nik'] ?? null;
        $tim->no_hp = $validated['no_hp'] ?? null;
        $tim->alamat = $validated['alamat'] ?? null;
        $tim->save();

        activity()
            ->performedOn($tim)
            ->event('updated')
            ->log("Mengubah data anggota tim: {$tim->nama} ({$validated['role']})");

        TeamChanged::dispatch($tim, 'updated', $oldKecamatanId, $oldDesaId);

        return redirect()->route('admin.tim.index')
            ->with('success', 'Anggota tim berhasil diperbarui.');
    }

    /**
     * Remove the specified resource in storage.
     */
    public function destroy(AnggotaTim $tim): RedirectResponse
    {
        $oldName = $tim->nama;
        $oldRole = $tim->role;

        DB::table('anggota_tim')->where('id', $tim->id)->delete();

        activity()
            ->event('deleted')
            ->log("Menghapus anggota tim: {$oldName} ({$oldRole})");

        TeamChanged::dispatch($tim, 'deleted');

        return redirect()->route('admin.tim.index')
            ->with('success', 'Anggota tim berhasil dihapus.');
    }
}
