<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Commands;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Drivers\TableDriver;
use Illuminate\Console\MigrationGeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Override;

class MakePreferencesTable extends MigrationGeneratorCommand
{
    protected $description = 'Create a migration for the preferences table of a model';

    protected $signature = 'model-preferences:table {model : The model class}';

    #[Override]
    protected function migrationStubFile(): string
    {
        return __DIR__.'/../../stubs/table-preferences.stub';
    }

    #[Override]
    protected function migrationTableName(): string
    {
        $model = $this->model();

        return new TableDriver()->tableName($model, $model->preferencesConfig());
    }

    protected function model(): HasPreferences&Model
    {
        $model = new ($this->argument('model'))();

        if (! $model instanceof Model || ! $model instanceof HasPreferences) {
            throw new InvalidArgumentException(
                sprintf(
                    'The [%s] class must be a model implementing [%s].',
                    $model::class,
                    HasPreferences::class,
                ),
            );
        }

        return $model;
    }

    /**
     * @param string $path
     * @param string $table
     */
    #[Override]
    protected function replaceMigrationPlaceholders($path, $table): void
    {
        $model = $this->model();

        $this->files->put($path, str_replace(
            ['{{ table }}', '{{ model }}'],
            [$table, '\\'.$model::class],
            $this->files->get($this->migrationStubFile()),
        ));
    }
}
