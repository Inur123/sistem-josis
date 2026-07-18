<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property string $nama
 * @property string $desa_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Desa $desa
 * @property-read DataSuara|null $dataSuara
 *
 * @mixin \Eloquent
 */
class Tps extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'tps';

    protected $fillable = ['nama', 'desa_id'];

    /**
     * @return BelongsTo<Desa, $this>
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    /**
     * @return HasOne<DataSuara, $this>
     */
    public function dataSuara(): HasOne
    {
        return $this->hasOne(DataSuara::class);
    }
}
