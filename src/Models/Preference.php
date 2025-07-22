<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * @property int $id
 * @property string $preferable_type
 * @property int $preferable_id
 * @property string $key
 * @property mixed $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Model $preferable
 */
class Preference extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    public function getTable(): string
    {
        return Config::string('model-preferences.table');
    }

    /**
     * Get the model that owns the preference.
     *
     * @return MorphTo<Model, $this>
     */
    public function preferable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * {@inheritDoc}
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }
}
