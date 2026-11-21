<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Drivers;

use HosmelQ\ModelPreferences\Support\Config;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Override;

class SharedTableDriver extends DatabaseDriver
{
    #[Override]
    public function tableName(Model $model, PreferencesConfig $config): string
    {
        return Config::storesSharedTable();
    }

    #[Override]
    protected function connection(Model $model): ConnectionInterface
    {
        return DB::connection(Config::storesSharedConnection());
    }

    /**
     * @return array<non-empty-string, string>
     */
    #[Override]
    protected function constraints(Model $model): array
    {
        return ['preferable_type' => $model->getMorphClass()];
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    protected function foreignKey(): string
    {
        return 'preferable_id';
    }
}
