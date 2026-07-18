<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $tps_id
 * @property int $total_suara
 * @property string|null $c_hasil_path
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Tps $tps
 *
 * @mixin \Eloquent
 */
class DataSuara extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'data_suaras';

    protected $fillable = ['tps_id', 'total_suara', 'c_hasil_path'];

    /**
     * @return BelongsTo<Tps, $this>
     */
    public function tps(): BelongsTo
    {
        return $this->belongsTo(Tps::class);
    }
}
