<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Commands;

use function Safe\file_get_contents;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Date;

class MakePreferencesTable extends Command
{
    /**
     * {@inheritDoc}
     */
    protected $description = 'Create a new preferences table migration';

    /**
     * {@inheritDoc}
     */
    protected $signature = 'model-preferences:table {name : The name of the preferences table}';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        $table = $this->argument('name');

        $this->writeMigration($files, $table);

        $this->components->info(sprintf('Migration [%s] created successfully.', $this->getMigrationFileName($table)));

        return self::SUCCESS;
    }

    /**
     * Get the migration file name.
     */
    protected function getMigrationFileName(string $table): string
    {
        return Date::now()->format('Y_m_d_His').'_create_'.$table.'_table.php';
    }

    /**
     * Get the migration file path.
     */
    protected function getMigrationPath(string $table): string
    {
        return database_path('migrations/'.$this->getMigrationFileName($table));
    }

    /**
     * Get the stub file contents.
     */
    protected function getStub(): string
    {
        return file_get_contents(__DIR__.'/../../stubs/table-preferences.stub');
    }

    /**
     * Populate the stub with the table name.
     */
    protected function populateStub(string $table): string
    {
        $stub = $this->getStub();

        return str_replace('{{ table }}', $table, $stub);
    }

    /**
     * Write the migration file to disk.
     */
    protected function writeMigration(Filesystem $files, string $table): void
    {
        $file = $this->getMigrationPath($table);

        $files->ensureDirectoryExists(dirname($file));

        $files->put($file, $this->populateStub($table));
    }
}
