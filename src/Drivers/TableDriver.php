<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Drivers;

use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Override;

class TableDriver extends DatabaseDriver
{
    #[Override]
    public function tableName(Model $model, PreferencesConfig $config): string
    {
        return $config->getTable() ?? Str::singular($model->getTable()).'_preferences';
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    protected function foreignKey(): string
    {
        return 'model_id';
    }
}
