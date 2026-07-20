<?php

namespace App\Http\Controllers\Admin;

use App\Events\TeamChanged;
use App\Http\Controllers\Controller;
use App\Models\AnggotaTim;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Services\AesGcmEncryption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        return Inertia::render('admin/tim/Index', [
            'korcams' => $korcams,
            'kordes' => $kordes,
            'relawans' => $relawans,
            'kecamatans' => $kecamatans,
            'desas' => $desas,
            'total_korcam' => AnggotaTim::where('role', 'korcam')->count(),
            'total_kordes' => AnggotaTim::where('role', 'kordes')->count(),
            'total_relawan' => AnggotaTim::where('role', 'relawan')->count(),
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
            ->with('with', 'Anggota tim berhasil dihapus.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        set_time_limit(180);

        $tempFile = tempnam(sys_get_temp_dir(), 'export_tim');
        if ($tempFile === false) {
            abort(500, 'Gagal membuat file temporary');
        }

        $writer = new Writer();
        $writer->openToFile($tempFile);

        // --- STYLING ---
        $headerStyle = (new Style())
            ->withFontBold(true)
            ->withBackgroundColor('FFFCD116'); // Golkar Yellow

        $subTotalStyle = (new Style())
            ->withFontBold(true)
            ->withBackgroundColor('FFFFDE7'); // Sangat muda kuning

        $grandTotalStyle = (new Style())
            ->withFontBold(true)
            ->withBackgroundColor('FFFF9C4'); // Kuning muda stabil

        $titleStyle = (new Style())
            ->withFontBold(true)
            ->withFontSize(14);

        $numberStyle = (new Style())
            ->withFormat('#,##0');

        $subTotalNumberStyle = (new Style())
            ->withFontBold(true)
            ->withBackgroundColor('FFFFDE7')
            ->withFormat('#,##0');

        $grandTotalNumberStyle = (new Style())
            ->withFontBold(true)
            ->withBackgroundColor('FFFF9C4')
            ->withFormat('#,##0');

        // --- 1. SHEET REKAP ---
        $rekapSheet = $writer->getCurrentSheet();
        $rekapSheet->setName('REKAP');
        $rekapSheet->setColumnWidth(6, 1);
        $rekapSheet->setColumnWidth(25, 2);
        $rekapSheet->setColumnWidth(18, 3);
        $rekapSheet->setColumnWidth(18, 4);
        $rekapSheet->setColumnWidth(18, 5);
        $rekapSheet->setColumnWidth(18, 6);

        $writer->addRow(Row::fromValuesWithStyle(['REKAPITULASI ANGGOTA TIM'], $titleStyle));
        $writer->addRow(Row::fromValues([]));

        $rekapHeaders = ['NO', 'KECAMATAN', 'TOTAL KORCAM', 'TOTAL KORDES', 'TOTAL RELAWAN', 'TOTAL ANGGOTA'];
        $writer->addRow(Row::fromValuesWithStyle($rekapHeaders, $headerStyle));

        // Fetch Kecamatan with Desas
        $kecamatans = Kecamatan::orderBy('nama', 'asc')->get();

        // Get members
        $search = $request->query('search');
        $query = DB::table('anggota_tim')
            ->leftJoin('kecamatans', 'anggota_tim.kecamatan_id', '=', 'kecamatans.id')
            ->leftJoin('desas', 'anggota_tim.desa_id', '=', 'desas.id');

        $rawMembers = $query->select([
            'anggota_tim.id',
            'anggota_tim.role',
            'anggota_tim.nama',
            'anggota_tim.nik',
            'anggota_tim.no_hp',
            'anggota_tim.alamat',
            'anggota_tim.kecamatan_id',
            'anggota_tim.desa_id',
            'kecamatans.nama as kecamatan_nama',
            'desas.nama as desa_nama',
        ])->get();

        $membersByRole = [
            'korcam' => [],
            'kordes' => [],
            'relawan' => [],
        ];

        $countsByKecamatan = [];
        foreach ($kecamatans as $kec) {
            $countsByKecamatan[$kec->id] = [
                'korcam' => 0,
                'kordes' => 0,
                'relawan' => 0,
            ];
        }

        foreach ($rawMembers as $row) {
            $nama = AesGcmEncryption::decrypt($row->nama);

            // Filter search
            if ($search && !is_numeric($search)) {
                if (!str_contains(strtolower((string) $nama), strtolower(is_string($search) ? $search : ''))) {
                    continue;
                }
            }
            if ($search && is_numeric($search)) {
                $nik = AesGcmEncryption::decrypt($row->nik);
                $noHp = AesGcmEncryption::decrypt($row->no_hp);
                if (!str_contains((string) $nik, $search) && !str_contains((string) $noHp, $search)) {
                    continue;
                }
            }

            $m = [
                'nama' => $nama,
                'nik' => AesGcmEncryption::decrypt($row->nik),
                'no_hp' => AesGcmEncryption::decrypt($row->no_hp),
                'alamat' => AesGcmEncryption::decrypt($row->alamat),
                'kecamatan_nama' => $row->kecamatan_nama,
                'desa_nama' => $row->desa_nama,
            ];

            $membersByRole[$row->role][] = $m;

            if ($row->kecamatan_id && isset($countsByKecamatan[$row->kecamatan_id])) {
                $countsByKecamatan[$row->kecamatan_id][$row->role]++;
            }
        }

        // Tulis Rekap
        $no = 1;
        $grandKorcam = 0;
        $grandKordes = 0;
        $grandRelawan = 0;
        $grandTotal = 0;

        $rekapRowStyles = [
            2 => $numberStyle,
            3 => $numberStyle,
            4 => $numberStyle,
            5 => $numberStyle,
        ];

        foreach ($kecamatans as $kec) {
            $c = $countsByKecamatan[$kec->id];
            $rowTotal = $c['korcam'] + $c['kordes'] + $c['relawan'];

            $writer->addRow(Row::fromValuesWithStyles([
                $no++,
                'Kec. ' . $kec->nama,
                $c['korcam'],
                $c['kordes'],
                $c['relawan'],
                $rowTotal,
            ], $rekapRowStyles));

            $grandKorcam += $c['korcam'];
            $grandKordes += $c['kordes'];
            $grandRelawan += $c['relawan'];
            $grandTotal += $rowTotal;
        }

        // Grand Total Row
        $writer->addRow(Row::fromValuesWithStyles([
            '',
            'GRAND TOTAL',
            $grandKorcam,
            $grandKordes,
            $grandRelawan,
            $grandTotal,
        ], [
            0 => $grandTotalStyle,
            1 => $grandTotalStyle,
            2 => $grandTotalNumberStyle,
            3 => $grandTotalNumberStyle,
            4 => $grandTotalNumberStyle,
            5 => $grandTotalNumberStyle,
        ]));

        // --- 2. SHEET KORCAM ---
        $this->writeRoleSheet($writer, 'KORCAM', 'DAFTAR KOORDINATOR KECAMATAN (KORCAM)', $membersByRole['korcam'], ['NO', 'NAMA', 'NIK', 'NO. HP', 'ALAMAT', 'KECAMATAN'], $titleStyle, $headerStyle, false);

        // --- 3. SHEET KORDES ---
        $this->writeRoleSheet($writer, 'KORDES', 'DAFTAR KOORDINATOR DESA (KORDES)', $membersByRole['kordes'], ['NO', 'NAMA', 'NIK', 'NO. HP', 'ALAMAT', 'KECAMATAN', 'DESA'], $titleStyle, $headerStyle, true);

        // --- 4. SHEET RELAWAN ---
        $this->writeRoleSheet($writer, 'RELAWAN', 'DAFTAR RELAWAN PENDUKUNG', $membersByRole['relawan'], ['NO', 'NAMA', 'NIK', 'NO. HP', 'ALAMAT', 'KECAMATAN', 'DESA'], $titleStyle, $headerStyle, true);

        $writer->close();

        $fileName = 'Data_Tim_Josis.xlsx';
        if ($search) {
            $fileName = 'Data_Tim_Josis_Filtered.xlsx';
        }

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    private function writeRoleSheet($writer, string $sheetName, string $title, array $members, array $headers, $titleStyle, $headerStyle, bool $includeDesa): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName($sheetName);
        $sheet->setColumnWidth(6, 1);
        $sheet->setColumnWidth(30, 2);
        $sheet->setColumnWidth(22, 3);
        $sheet->setColumnWidth(20, 4);
        $sheet->setColumnWidth(35, 5);
        $sheet->setColumnWidth(25, 6);
        if ($includeDesa) {
            $sheet->setColumnWidth(25, 7);
        }

        $writer->addRow(Row::fromValuesWithStyle([$title], $titleStyle));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValuesWithStyle($headers, $headerStyle));

        // Sort by nama
        usort($members, function ($a, $b) {
            return strcmp($a['nama'], $b['nama']);
        });

        $no = 1;
        foreach ($members as $m) {
            $rowValues = [
                $no++,
                $m['nama'],
                $m['nik'] ? $m['nik'] . ' ' : '-',
                $m['no_hp'] ? $m['no_hp'] . ' ' : '-',
                $m['alamat'] ?: '-',
                $m['kecamatan_nama'] ? 'Kec. ' . $m['kecamatan_nama'] : '-',
            ];
            if ($includeDesa) {
                $rowValues[] = $m['desa_nama'] ?: '-';
            }
            $writer->addRow(Row::fromValues($rowValues));
        }
    }
}
