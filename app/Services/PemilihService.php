<?php

namespace App\Services;

use App\Models\Pemilih;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PemilihService
{
    /**
     * Build a paginated + summary result for pemilih list.
     * Works for Admin, Kecamatan, and Desa with different scope constraints.
     *
     * @param  Request  $request
     * @param  array<string,mixed>  $scope  e.g. ['desa_id' => x] or ['kecamatan_id' => x]
     * @param  array<string>  $extraColumns  extra columns to include in result (e.g. ['kecamatan', 'desa'])
     * @return array{paginated: LengthAwarePaginator, summary: array{total:int, l:int, p:int}}
     */
    public function paginate(Request $request, array $scope = [], array $extraColumns = []): array
    {
        $search  = $request->query('search');
        $perPage = 20;

        /** @var \Illuminate\Database\Eloquent\Builder<Pemilih> $query */
        $query = Pemilih::query()->with(['kecamatan', 'desa'])->orderBy('created_at', 'desc');

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
            'id'            => $p->id,
            'nik'           => $p->nik,
            'nama'          => $p->nama,
            'jenis_kelamin' => $p->jenis_kelamin,
            'alamat'        => $p->alamat,
            'rt'            => $p->rt,
            'rw'            => $p->rw,
            'kecamatan'     => in_array('kecamatan', $extraColumns) ? $p->kecamatan?->nama : null,
            'desa'          => in_array('desa', $extraColumns) ? $p->desa?->nama : null,
            'created_at'    => $p->created_at?->format('d/m/Y'),
        ], fn ($v) => $v !== null);

        if ($search && !is_numeric($search)) {
            // In-memory name search (required because nama is encrypted)
            $all = $query->get()->filter(
                fn ($p) => str_contains(strtolower($p->nama), strtolower($search))
            );

            $countTotal    = $all->count();
            $countL        = $all->where('jenis_kelamin', 'L')->count();
            $countP        = $all->where('jenis_kelamin', 'P')->count();
            $currentPage   = (int) $request->query('page', 1);

            $paginated = new LengthAwarePaginator(
                $all->slice(($currentPage - 1) * $perPage, $perPage)->map($formatRow)->values(),
                $countTotal,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $countTotal  = $query->count();
            $countL      = (clone $query)->where('jenis_kelamin', 'L')->count();
            $countP      = (clone $query)->where('jenis_kelamin', 'P')->count();
            $paginated   = $query->paginate($perPage)->through($formatRow);
        }

        return [
            'paginated' => $paginated,
            'summary'   => [
                'total' => $countTotal,
                'l'     => $countL,
                'p'     => $countP,
            ],
        ];
    }

    /**
     * Store a new pemilih.
     *
     * @param  array<string,mixed>  $data  validated data
     * @param  string  $desaId
     * @param  string  $kecamatanId
     * @param  string  $userId
     * @return array{error: string}|null  returns error array or null on success
     */
    public function store(array $data, string $desaId, string $kecamatanId, string $userId): ?array
    {
        $nikHash = hash('sha256', $data['nik']);

        if (DB::table('pemilihs')->where('nik_hash', $nikHash)->exists()) {
            return ['nik' => 'NIK sudah terdaftar dalam sistem.'];
        }

        Pemilih::create([
            'nik'           => $data['nik'],
            'nik_hash'      => $nikHash,
            'nama'          => $data['nama'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'alamat'        => $data['alamat'],
            'rt'            => $data['rt'],
            'rw'            => $data['rw'],
            'desa_id'       => $desaId,
            'kecamatan_id'  => $kecamatanId,
            'user_id'       => $userId,
        ]);

        return null;
    }

    /**
     * Update an existing pemilih.
     *
     * @param  Pemilih  $pemilih
     * @param  array<string,mixed>  $data
     * @return array{error: string}|null
     */
    public function update(Pemilih $pemilih, array $data): ?array
    {
        $nikHash = hash('sha256', $data['nik']);

        if (DB::table('pemilihs')->where('nik_hash', $nikHash)->where('id', '!=', $pemilih->id)->exists()) {
            return ['nik' => 'NIK sudah terdaftar untuk pemilih lain.'];
        }

        $pemilih->nik           = $data['nik'];
        $pemilih->nik_hash      = $nikHash;
        $pemilih->nama          = $data['nama'];
        $pemilih->jenis_kelamin = $data['jenis_kelamin'];
        $pemilih->alamat        = $data['alamat'];
        $pemilih->rt            = $data['rt'];
        $pemilih->rw            = $data['rw'];
        $pemilih->save();

        return null;
    }

    /**
     * Delete a pemilih record.
     */
    public function destroy(Pemilih $pemilih): void
    {
        DB::table('pemilihs')->where('id', $pemilih->id)->delete();
    }
}
