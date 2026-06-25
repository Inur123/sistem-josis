<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Pemilih;
use App\Services\AesGcmEncryption;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PemilihController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        // Cek jika request adalah AJAX fetch biasa dari Vue (bukan navigasi Inertia)
        if ($request->header('X-Requested-With') === 'XMLHttpRequest' && ! $request->header('X-Inertia')) {
            $result = $this->paginate($request, [], ['kecamatan', 'desa']);

            return response()->json([
                'paginated' => $result['paginated'],
                'summary' => $result['summary'],
            ]);
        }

        $result = $this->paginate($request, [], ['kecamatan', 'desa']);

        /** @var Builder<Kecamatan> $kecamatanQuery */
        $kecamatanQuery = Kecamatan::query();

        /** @var Builder<Desa> $desaQuery */
        $desaQuery = Desa::query();

        return Inertia::render('admin/Pemilih', [
            'pemilihs' => $result['paginated'],
            'kecamatans' => $kecamatanQuery->orderBy('nama', 'asc')->get(['id', 'nama']),
            'desas' => $desaQuery->orderBy('nama', 'asc')->get(['id', 'nama', 'kecamatan_id']),
            'filters' => [
                'kecamatan_id' => $request->query('kecamatan_id'),
                'desa_id' => $request->query('desa_id'),
                'search' => $request->query('search'),
            ],
            'summary' => $result['summary'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $scope
     * @param  array<int, string>  $extraColumns
     * @return array{paginated: \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, mixed>, summary: array{total: int, l: int, p: int}}
     */
    private function paginate(Request $request, array $scope = [], array $extraColumns = []): array
    {
        $search = $request->query('search');
        $perPage = 20;

        /** @var Builder<Pemilih> $query */
        $query = Pemilih::query()->with(['kecamatan', 'desa', 'relawan'])->orderBy('created_at', 'desc');

        foreach ($scope as $column => $value) {
            if ($value) {
                $query->where($column, $value);
            }
        }

        // Extra optional filters passed via request
        if ($request->query('desa_id')) {
            $query->where('desa_id', $request->query('desa_id'));
        }
        if ($request->query('kecamatan_id')) {
            $query->where('kecamatan_id', $request->query('kecamatan_id'));
        }
        if ($request->query('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->query('jenis_kelamin'));
        }

        if ($search && is_numeric($search)) {
            $query->where('nik_hash', hash('sha256', $search));
        }

        $formatRow = fn ($p) => array_filter([
            'id' => $p->id,
            'nik' => $p->nik,
            'nama' => $p->nama,
            'jenis_kelamin' => $p->jenis_kelamin,
            'alamat' => $p->alamat,
            'rt' => $p->rt,
            'rw' => $p->rw,
            'kecamatan' => in_array('kecamatan', $extraColumns) ? $p->kecamatan?->nama : null,
            'desa' => in_array('desa', $extraColumns) ? $p->desa?->nama : null,
            'relawan' => $p->relawan?->nama,
            'created_at' => $p->created_at?->format('d/m/Y'),
        ], fn ($v) => $v !== null);

        if ($search && ! is_numeric($search)) {
            // In-memory name search (required because nama is encrypted)
            $all = $query->get()->filter(
                fn ($p) => str_contains(strtolower($p->nama), strtolower($search))
            );

            $countTotal = $all->count();
            $countL = $all->where('jenis_kelamin', 'L')->count();
            $countP = $all->where('jenis_kelamin', 'P')->count();
            $currentPage = (int) $request->query('page', 1);

            $paginated = new LengthAwarePaginator(
                $all->slice(($currentPage - 1) * $perPage, $perPage)->map($formatRow)->values(),
                $countTotal,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $countTotal = $query->count();
            $countL = (clone $query)->where('jenis_kelamin', 'L')->count();
            $countP = (clone $query)->where('jenis_kelamin', 'P')->count();
            $paginated = $query->paginate($perPage)->through($formatRow);
        }

        return [
            'paginated' => $paginated,
            'summary' => [
                'total' => $countTotal,
                'l' => $countL,
                'p' => $countP,
            ],
        ];
    }

    public function export(Request $request): BinaryFileResponse
    {
        set_time_limit(180);

        $tempFile = tempnam(sys_get_temp_dir(), 'export_pemilih');
        if ($tempFile === false) {
            abort(500, 'Gagal membuat file temporary');
        }

        $writer = new Writer;
        $writer->openToFile($tempFile);

        // --- STYLING ---
        $headerStyle = (new Style)
            ->withFontBold(true)
            ->withBackgroundColor('FFFCD116'); // Golkar Yellow

        $subTotalStyle = (new Style)
            ->withFontBold(true)
            ->withBackgroundColor('FFFFDE7'); // Sangat muda kuning

        $grandTotalStyle = (new Style)
            ->withFontBold(true)
            ->withBackgroundColor('FFFF9C4'); // Kuning muda stabil

        $titleStyle = (new Style)
            ->withFontBold(true)
            ->withFontSize(14);

        $numberStyle = (new Style)
            ->withFormat('#,##0');

        $subTotalNumberStyle = (new Style)
            ->withFontBold(true)
            ->withBackgroundColor('FFFFDE7') // Sangat muda kuning
            ->withFormat('#,##0');

        $grandTotalNumberStyle = (new Style)
            ->withFontBold(true)
            ->withBackgroundColor('FFFF9C4') // Kuning muda stabil
            ->withFormat('#,##0');

        // --- 1. SHEET REKAP ---
        $rekapSheet = $writer->getCurrentSheet();
        $rekapSheet->setName('REKAP');
        $rekapSheet->setColumnWidth(6, 1);
        $rekapSheet->setColumnWidth(25, 2);
        $rekapSheet->setColumnWidth(25, 3);
        $rekapSheet->setColumnWidth(15, 4);
        $rekapSheet->setColumnWidth(15, 5);
        $rekapSheet->setColumnWidth(20, 6);

        // Header REKAP
        $writer->addRow(Row::fromValuesWithStyle(['REKAPITULASI DATA PEMILIH'], $titleStyle));
        $writer->addRow(Row::fromValues([]));

        $rekapHeaders = ['NO', 'KECAMATAN', 'DESA / KELURAHAN', 'LAKI-LAKI', 'PEREMPUAN', 'TOTAL PEMILIH'];
        $writer->addRow(Row::fromValuesWithStyle($rekapHeaders, $headerStyle));

        // Fetch Kecamatan with Desas
        $kecamatans = Kecamatan::with(['desas' => fn ($q) => $q->orderBy('nama', 'asc')])->orderBy('nama', 'asc')->get();

        $search = $request->query('search');

        // Load ALL data pemilih dengan single query menggunakan raw DB (hemat memori luar biasa)
        $query = DB::table('pemilihs')
            ->leftJoin('kecamatans', 'pemilihs.kecamatan_id', '=', 'kecamatans.id')
            ->leftJoin('desas', 'pemilihs.desa_id', '=', 'desas.id')
            ->leftJoin('anggota_tim', 'pemilihs.relawan_id', '=', 'anggota_tim.id');

        if ($request->query('kecamatan_id')) {
            $query->where('pemilihs.kecamatan_id', $request->query('kecamatan_id'));
        }
        if ($request->query('desa_id')) {
            $query->where('pemilihs.desa_id', $request->query('desa_id'));
        }
        if ($search && is_numeric($search)) {
            $query->where('pemilihs.nik_hash', hash('sha256', $search));
        }

        $rawVoters = $query->select([
            'pemilihs.id',
            'pemilihs.nik',
            'pemilihs.nama',
            'pemilihs.jenis_kelamin',
            'pemilihs.alamat',
            'pemilihs.rt',
            'pemilihs.rw',
            'pemilihs.created_at',
            'pemilihs.kecamatan_id',
            'pemilihs.desa_id',
            'kecamatans.nama as kecamatan_nama',
            'desas.nama as desa_nama',
            'anggota_tim.nama as relawan_nama',
        ])->get();

        $votersByKecamatan = [];
        $votersByDesa = [];

        foreach ($rawVoters as $row) {
            $nama = AesGcmEncryption::decrypt($row->nama);

            // Terapkan filter pencarian nama jika aktif
            if ($search && ! is_numeric($search)) {
                if (! str_contains(strtolower((string) $nama), strtolower(is_string($search) ? $search : ''))) {
                    continue;
                }
            }

            $v = [
                'nik' => AesGcmEncryption::decrypt($row->nik),
                'nama' => $nama,
                'jenis_kelamin' => $row->jenis_kelamin,
                'alamat' => AesGcmEncryption::decrypt($row->alamat),
                'rt' => AesGcmEncryption::decrypt($row->rt),
                'rw' => AesGcmEncryption::decrypt($row->rw),
                'created_at' => $row->created_at ? CarbonImmutable::parse($row->created_at)->locale('id')->translatedFormat('d F Y') : '-',
                'kecamatan_nama' => $row->kecamatan_nama,
                'desa_nama' => $row->desa_nama,
                'relawan_nama' => $row->relawan_nama ? AesGcmEncryption::decrypt($row->relawan_nama) : '-',
            ];

            $votersByKecamatan[$row->kecamatan_id][] = $v;
            $votersByDesa[$row->desa_id][] = $v;
        }
        unset($rawVoters);
        gc_collect_cycles();

        // Tulis data REKAP menggunakan data in-memory
        $no = 1;

        $grandL = 0;
        $grandP = 0;
        $grandTotal = 0;

        $rekapRowStyles = [
            3 => $numberStyle,
            4 => $numberStyle,
            5 => $numberStyle,
        ];

        foreach ($kecamatans as $kec) {
            $kecL = 0;
            $kecP = 0;
            $kecTotal = 0;

            foreach ($kec->desas as $desa) {
                $desaVoters = $votersByDesa[$desa->id] ?? [];
                $dL = 0;
                $dP = 0;

                foreach ($desaVoters as $dv) {
                    if ($dv['jenis_kelamin'] === 'L') {
                        $dL++;
                    } elseif ($dv['jenis_kelamin'] === 'P') {
                        $dP++;
                    }
                }

                $dTotal = $dL + $dP;

                $writer->addRow(Row::fromValuesWithStyles([
                    $no++,
                    'Kec. '.$kec->nama,
                    $desa->nama,
                    $dL,
                    $dP,
                    $dTotal,
                ], $rekapRowStyles));

                $kecL += $dL;
                $kecP += $dP;
                $kecTotal += $dTotal;
            }

            // Subtotal Kecamatan
            $writer->addRow(Row::fromValuesWithStyles([
                '',
                'TOTAL KEC. '.$kec->nama,
                '',
                $kecL,
                $kecP,
                $kecTotal,
            ], [
                0 => $subTotalStyle,
                1 => $subTotalStyle,
                2 => $subTotalStyle,
                3 => $subTotalNumberStyle,
                4 => $subTotalNumberStyle,
                5 => $subTotalNumberStyle,
            ]));

            $grandL += $kecL;
            $grandP += $kecP;
            $grandTotal += $kecTotal;
        }

        // Grand Total
        $writer->addRow(Row::fromValuesWithStyles([
            '',
            'GRAND TOTAL',
            '',
            $grandL,
            $grandP,
            $grandTotal,
        ], [
            0 => $grandTotalStyle,
            1 => $grandTotalStyle,
            2 => $grandTotalStyle,
            3 => $grandTotalNumberStyle,
            4 => $grandTotalNumberStyle,
            5 => $grandTotalNumberStyle,
        ]));

        // --- 2. SHEET PER KECAMATAN ---
        foreach ($kecamatans as $kec) {
            if ($request->query('kecamatan_id') && $request->query('kecamatan_id') !== $kec->id) {
                continue;
            }

            $kecVoters = $votersByKecamatan[$kec->id] ?? [];
            if (empty($kecVoters)) {
                continue;
            }

            // Sort in-memory
            usort($kecVoters, function ($a, $b) {
                $cmp = strcmp((string) ($a['desa_nama'] ?? ''), (string) ($b['desa_nama'] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }

                return strcmp($a['nama'], $b['nama']);
            });

            $kecSheet = $writer->addNewSheetAndMakeItCurrent();
            $kecSheet->setName($this->sanitizeSheetTitle('Kec. '.$kec->nama));
            $kecSheet->setColumnWidth(6, 1);
            $kecSheet->setColumnWidth(22, 2);
            $kecSheet->setColumnWidth(30, 3);
            $kecSheet->setColumnWidth(20, 4);
            $kecSheet->setColumnWidth(25, 5);
            $kecSheet->setColumnWidth(35, 6);
            $kecSheet->setColumnWidth(12, 7);
            $kecSheet->setColumnWidth(25, 8);
            $kecSheet->setColumnWidth(20, 9);

            $writer->addRow(Row::fromValuesWithStyle(['DATA PEMILIH KECAMATAN '.strtoupper($kec->nama)], $titleStyle));
            $writer->addRow(Row::fromValues([]));

            $headers = ['NO', 'NIK', 'NAMA LENGKAP', 'JENIS KELAMIN', 'DESA / KELURAHAN', 'ALAMAT', 'RT/RW', 'RELAWAN', 'TANGGAL INPUT'];
            $writer->addRow(Row::fromValuesWithStyle($headers, $headerStyle));

            $kNo = 1;
            foreach ($kecVoters as $p) {
                $writer->addRow(Row::fromValues([
                    $kNo++,
                    $p['nik'],
                    $p['nama'],
                    $p['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($p['jenis_kelamin'] === 'P' ? 'Perempuan' : $p['jenis_kelamin']),
                    $p['desa_nama'],
                    $p['alamat'],
                    "{$p['rt']}/{$p['rw']}",
                    $p['relawan_nama'],
                    $p['created_at'],
                ]));
            }
        }

        // --- 3. SHEET PER DESA ---
        foreach ($kecamatans as $kec) {
            if ($request->query('kecamatan_id') && $request->query('kecamatan_id') !== $kec->id) {
                continue;
            }

            foreach ($kec->desas as $desa) {
                if ($request->query('desa_id') && $request->query('desa_id') !== $desa->id) {
                    continue;
                }

                $desaVoters = $votersByDesa[$desa->id] ?? [];
                if (empty($desaVoters)) {
                    continue;
                }

                // Sort in-memory
                usort($desaVoters, function ($a, $b) {
                    return strcmp($a['nama'], $b['nama']);
                });

                $desaSheet = $writer->addNewSheetAndMakeItCurrent();
                $desaSheet->setName($this->sanitizeSheetTitle($desa->nama));
                $desaSheet->setColumnWidth(6, 1);
                $desaSheet->setColumnWidth(22, 2);
                $desaSheet->setColumnWidth(30, 3);
                $desaSheet->setColumnWidth(20, 4);
                $desaSheet->setColumnWidth(35, 5);
                $desaSheet->setColumnWidth(12, 6);
                $desaSheet->setColumnWidth(25, 7);
                $desaSheet->setColumnWidth(20, 8);

                $writer->addRow(Row::fromValuesWithStyle(['DATA PEMILIH DESA '.strtoupper($desa->nama).' (KEC. '.strtoupper($kec->nama).')'], $titleStyle));
                $writer->addRow(Row::fromValues([]));

                $dHeaders = ['NO', 'NIK', 'NAMA LENGKAP', 'JENIS KELAMIN', 'ALAMAT', 'RT/RW', 'RELAWAN', 'TANGGAL INPUT'];
                $writer->addRow(Row::fromValuesWithStyle($dHeaders, $headerStyle));

                $dNo = 1;
                foreach ($desaVoters as $p) {
                    $writer->addRow(Row::fromValues([
                        $dNo++,
                        $p['nik'],
                        $p['nama'],
                        $p['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($p['jenis_kelamin'] === 'P' ? 'Perempuan' : $p['jenis_kelamin']),
                        $p['alamat'],
                        "{$p['rt']}/{$p['rw']}",
                        $p['relawan_nama'],
                        $p['created_at'],
                    ]));
                }
            }
        }

        $writer->close();
        unset($writer);
        gc_collect_cycles();

        $filename = 'Data_Pemilih_Josis_'.date('Ymd_His').'.xlsx';

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    private function sanitizeSheetTitle(string $title): string
    {
        // Karakter terlarang di Excel: \ / ? * : [ ]
        $clean = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '', $title);

        return substr($clean, 0, 31);
    }
}
