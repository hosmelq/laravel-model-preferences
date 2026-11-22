<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Support;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use Override;

class JsonExpression implements Expression
{
    /**
     * @param list<mixed> $bindings
     */
    protected function __construct(protected string $sql, public array $bindings) {}

    public static function equals(
        Builder $query,
        string $column,
        null|string $key,
        mixed $value,
    ): self {
        $grammar = $query->getGrammar();
        $column = $grammar->wrap($column);
        $json = json_encode($value, JSON_THROW_ON_ERROR);
        $path = self::path($grammar, $key);

        return match (true) {
            // MariaDB keeps rows where JSON_EQUALS() returns null unless explicitly compared.
            $query->getConnection() instanceof MySqlConnection
                && $query->getConnection()->isMaria() => new self(
                    "json_equals(json_extract({$column}, ?), ?) = 1",
                    [$path, $json],
                ),
            $grammar instanceof MySqlGrammar => new self(
                "json_extract({$column}, ?) = cast(? as json)",
                [$path, $json],
            ),
            $grammar instanceof PostgresGrammar => new self(
                "jsonb_path_query_first({$column}::jsonb, ?::jsonpath, '{}', true) = ?::jsonb",
                [$path, $json],
            ),
            $grammar instanceof SqlServerGrammar => self::sqlServerEquals($column, $key, $json),
            $key === null => new self(
                "json_type({$column}) = json_type(?) and json_extract({$column}, '$') is json_extract(?, '$')",
                [$json, $json],
            ),
            // SQLite < 3.45 misreads quoted path keys, so match via json_each().
            default => new self(
                "exists (select 1 from json_each({$column}) where json_each.key = ? and json_each.type = json_type(?) and json_each.value is json_extract(?, '$'))",
                [$key, $json, $json],
            ),
        };
    }

    public static function lacksKey(Builder $query, string $column, string $key): self
    {
        $grammar = $query->getGrammar();
        $column = $grammar->wrap($column);
        $path = self::path($grammar, $key);

        return match (true) {
            $grammar instanceof MySqlGrammar => new self(
                "not coalesce(json_contains_path({$column}, 'one', ?), 0)",
                [$path],
            ),
            $grammar instanceof PostgresGrammar => new self(
                "not coalesce(jsonb_path_exists({$column}::jsonb, ?::jsonpath, '{}', true), false)",
                [$path],
            ),
            $grammar instanceof SqlServerGrammar => new self(
                "not exists (select 1 from openjson({$column}) where "
                    .self::sqlServerText('[key]')
                    .')',
                [$key],
            ),
            default => new self(
                "not exists (select 1 from json_each({$column}) where json_each.key = ?)",
                [$key],
            ),
        };
    }

    #[Override]
    public function getValue(Grammar $grammar): string
    {
        return $this->sql;
    }

    protected static function path(Grammar $grammar, null|string $key): string
    {
        $path = $key === null ? '$' : '$.'.json_encode($key, JSON_THROW_ON_ERROR);

        return $grammar instanceof PostgresGrammar ? 'strict '.$path : $path;
    }

    protected static function sqlServerEquals(string $column, null|string $key, string $json): self
    {
        if ($key === null) {
            return new self(self::sqlServerText($column), [$json]);
        }

        $value = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        $type = match (true) {
            $value === null => 0,
            is_string($value) => 1,
            is_bool($value) => 3,
            is_array($value) => 4,
            is_object($value) => 5,
            default => 2,
        };

        if ($type === 0) {
            return new self(
                'exists (select 1 from openjson('
                    .$column
                    .') where '
                    .self::sqlServerText('[key]')
                    .' and [type] = 0)',
                [$key],
            );
        }

        return new self(
            'exists (select 1 from openjson('
                .$column
                .') where '
                .self::sqlServerText('[key]')
                .' and [type] = ? and '
                .self::sqlServerText('[value]')
                .')',
            [$key, $type, is_string($value) ? $value : $json],
        );
    }

    protected static function sqlServerText(string $expression): string
    {
        return "concat({$expression}, '#') = concat(?, '#') collate Latin1_General_100_BIN2";
    }
}
