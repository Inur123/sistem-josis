<?php

namespace App\Models;

use App\Casts\EncryptedAesGcm;
use App\Events\ActivityLogged;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    use HasUlids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'description' => EncryptedAesGcm::class,
        ]);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Activity $activity) {
            ActivityLogged::dispatch($activity);
        });
    }
}
