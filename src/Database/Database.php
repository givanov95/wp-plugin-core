<?php

namespace WpPluginCore\Database;

use InvalidArgumentException;
use RuntimeException;

class Database
{
    protected string $table;

    /**
     * Columns that are allowed in WHERE / ORDER BY clauses.
     *
     * If null, identifiers are validated only against a safe regex
     * ([A-Za-z_][A-Za-z0-9_]*). Pass an explicit list for stricter safety.
     *
     * @var string[]|null
     */
    protected ?array $allowedColumns;

    /**
     * @param string        $table           Table name without the WP prefix
     * @param string[]|null $allowedColumns  Optional allowlist of column names
     */
    public function __construct(string $table, ?array $allowedColumns = null)
    {
        global $wpdb;
        $this->table = $wpdb->prefix . $table;
        $this->allowedColumns = $allowedColumns;
    }

    public function insert(array $data): int
    {
        global $wpdb;

        $formats = $this->detectFormats($data);

        if ($wpdb->insert($this->table, $data, $formats) === false) {
            throw new RuntimeException($wpdb->last_error);
        }

        return (int) $wpdb->insert_id;
    }

    public function update(array $data, array $where): int
    {
        global $wpdb;

        $result = $wpdb->update(
            $this->table,
            $data,
            $where,
            $this->detectFormats($data),
            $this->detectFormats($where)
        );

        if ($result === false) {
            throw new RuntimeException($wpdb->last_error);
        }

        return (int) $result;
    }

    public function delete(array $where): int
    {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table,
            $where,
            $this->detectFormats($where)
        );

        if ($result === false) {
            throw new RuntimeException($wpdb->last_error);
        }

        return (int) $result;
    }

    public function find(int|string $id, string $column = 'id'): ?array
    {
        $rows = $this->where([$column => $id]);
        return $rows[0] ?? null;
    }

    public function first(array $conditions): ?array
    {
        $rows = $this->where($conditions);
        return $rows[0] ?? null;
    }

    public function where(array $conditions): array
    {
        global $wpdb;

        [$sql, $values, $formats] = $this->buildWhere($conditions);

        $query = "SELECT * FROM {$this->table}";
        if ($sql !== '') {
            $query .= " WHERE {$sql}";
        }

        if ($values === []) {
            return $wpdb->get_results($query, ARRAY_A) ?: [];
        }

        $prepared = $wpdb->prepare($query, ...$values);
        return $wpdb->get_results($prepared, ARRAY_A) ?: [];
    }

    public function count(array $conditions = []): int
    {
        global $wpdb;

        [$sql, $values] = $this->buildWhere($conditions);

        $query = "SELECT COUNT(*) FROM {$this->table}";
        if ($sql !== '') {
            $query .= " WHERE {$sql}";
        }

        if ($values === []) {
            return (int) $wpdb->get_var($query);
        }

        return (int) $wpdb->get_var($wpdb->prepare($query, ...$values));
    }

    /**
     * @param array            $conditions key/value pairs joined by AND
     * @param int              $page       1-based page index
     * @param int              $perPage    items per page (clamped to >= 1)
     * @param array|null       $orderBy    [column => 'ASC'|'DESC'], default ['id' => 'DESC']
     */
    public function paginate(
        array $conditions,
        int $page = 1,
        int $perPage = 10,
        ?array $orderBy = null
    ): array {
        global $wpdb;

        $perPage = max(1, $perPage);
        $page    = max(1, $page);
        $offset  = ($page - 1) * $perPage;

        [$sql, $values] = $this->buildWhere($conditions);

        $whereClause = $sql !== '' ? "WHERE {$sql}" : '';
        $orderClause = $this->buildOrderBy($orderBy ?? ['id' => 'DESC']);

        $query = "SELECT * FROM {$this->table} {$whereClause} {$orderClause} LIMIT %d OFFSET %d";

        $values[] = $perPage;
        $values[] = $offset;

        return $wpdb->get_results($wpdb->prepare($query, ...$values), ARRAY_A) ?: [];
    }

    /**
     * @return array{0:string,1:array,2:array}
     */
    private function buildWhere(array $conditions): array
    {
        $parts   = [];
        $values  = [];
        $formats = [];

        foreach ($conditions as $col => $value) {
            $column   = $this->escapeIdentifier($col);
            $format   = $this->formatFor($value);
            $parts[]  = "{$column} = {$format}";
            $values[] = $value;
            $formats[] = $format;
        }

        return [implode(' AND ', $parts), $values, $formats];
    }

    private function buildOrderBy(array $orderBy): string
    {
        $parts = [];
        foreach ($orderBy as $col => $direction) {
            $column = $this->escapeIdentifier((string) $col);
            $dir = strtoupper((string) $direction) === 'ASC' ? 'ASC' : 'DESC';
            $parts[] = "{$column} {$dir}";
        }

        return $parts === [] ? '' : 'ORDER BY ' . implode(', ', $parts);
    }

    private function escapeIdentifier(string $identifier): string
    {
        if ($this->allowedColumns !== null) {
            if (!in_array($identifier, $this->allowedColumns, true)) {
                throw new InvalidArgumentException("Column '{$identifier}' is not allowed.");
            }
            return "`{$identifier}`";
        }

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new InvalidArgumentException("Invalid column identifier: '{$identifier}'.");
        }

        return "`{$identifier}`";
    }

    private function detectFormats(array $data): array
    {
        return array_map([$this, 'formatFor'], $data);
    }

    private function formatFor(mixed $value): string
    {
        return match (true) {
            is_int($value), is_bool($value) => '%d',
            is_float($value)                => '%f',
            default                         => '%s',
        };
    }
}
